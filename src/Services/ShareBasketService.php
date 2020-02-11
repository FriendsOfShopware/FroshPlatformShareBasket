<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem\ShareBasketLineItemEntity;
use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketDefinition;
use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\Exception\InvalidPayloadException;
use Shopware\Core\Checkout\Cart\Exception\InvalidQuantityException;
use Shopware\Core\Checkout\Cart\Exception\LineItemNotStackableException;
use Shopware\Core\Checkout\Cart\Exception\MixedLineItemTypeException;
use Shopware\Core\Checkout\Cart\Exception\PayloadKeyNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\DataCollectorTranslator;

class ShareBasketService implements ShareBasketServiceInterface
{
    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var EntityRepositoryInterface
     */
    private $repository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var DataCollectorTranslator
     */
    private $dataCollectorTranslator;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        CartService $cartService,
        EntityRepositoryInterface $repository,
        RouterInterface $router,
        Session $session,
        DataCollectorTranslator $dataCollectorTranslator,
        SalesChannelRepositoryInterface $productRepository
    ) {
        $this->cartService = $cartService;
        $this->repository = $repository;
        $this->router = $router;
        $this->session = $session;
        $this->dataCollectorTranslator = $dataCollectorTranslator;
        $this->productRepository = $productRepository;
    }

    public function saveCart(SalesChannelContext $salesChannelContext): ?string
    {
        $data = $this->prepareLineItems($salesChannelContext);
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannel()->getId()));
        $criteria->addFilter(new EqualsFilter('hash', $data['hash']));

        $shareBasketEntity = $this->repository->search($criteria, $salesChannelContext->getContext())->first();
        if ($shareBasketEntity instanceof ShareBasketEntity) {
            $data['id'] = $shareBasketEntity->getId();
            $data['basketId'] = $shareBasketEntity->getBasketId();
            if ($this->session->get('froshShareBasketHash') !== $data['hash']) {
                $data['saveCount'] = $shareBasketEntity->increaseSaveCount();
            }

            unset($data['lineItems']);
            $this->repository->update([$data], $salesChannelContext->getContext());
            $this->session->set('froshShareBasketHash', $data['hash']);

            return $this->generateBasketUrl($data['basketId']);
        }

        return $this->persistCart($salesChannelContext->getContext(), $data);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function loadCart(Request $request, SalesChannelContext $salesChannelContext): ?Cart
    {
        if (!$request->attributes->has('basketId')) {
            throw new \InvalidArgumentException('Parameter basketId missing');
        }

        $basketId = $request->attributes->get('basketId');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannel()->getId()));
        $criteria->addFilter(new EqualsFilter('basketId', $basketId));
        $criteria->addAssociation('lineItems');

        /** @var ShareBasketEntity|null $shareBasketEntity */
        $shareBasketEntity = $this->repository->search($criteria, $salesChannelContext->getContext())->first();

        if (!$shareBasketEntity instanceof ShareBasketEntity) {
            throw new \RuntimeException(sprintf('Could not found a shared basket with id %s', $basketId));
        }

        $this->session->set('froshShareBasketHash', $shareBasketEntity->getHash());

        $token = $request->request->getAlnum('token', $salesChannelContext->getToken());
        $name = $request->request->getAlnum('name', CartService::SALES_CHANNEL);

        $this->cartService->createNew($token, $name);
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        $this->addLineItems($cart, $salesChannelContext, $shareBasketEntity);

        return $this->traceErrors($this->cartService->recalculate($cart, $salesChannelContext));
    }

    /**
     * @throws PayloadKeyNotFoundException
     */
    public function prepareLineItems(SalesChannelContext $salesChannelContext): array
    {
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        $lineItems = [];
        foreach ($cart->getLineItems() as $lineItem) {
            $identifier = false;

            if ($lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                $identifier = $lineItem->getPayloadValue('productNumber');
            }

            if ($lineItem->getType() === PromotionProcessor::LINE_ITEM_TYPE) {
                $identifier = $lineItem->getPayloadValue('code');
            }

            if (!$identifier) {
                continue;
            }

            $lineItems[] = [
                'identifier' => $identifier,
                'quantity' => $lineItem->getQuantity(),
                'type' => $lineItem->getType(),
                'removable' => $lineItem->isRemovable(),
                'stackable' => $lineItem->isStackable(),
            ];
        }

        usort($lineItems, static function ($a, $b) {
           return strcmp($a['identifier'],$b['identifier']);
        });

        return [
            'basketId' => $this->generateShareBasketId(),
            'hash' => sha1(serialize($lineItems)),
            'salesChannelId' => $salesChannelContext->getSalesChannel()->getId(),
            'lineItems' => $lineItems,
        ];
    }

    private function addLineItems(
        Cart $cart,
        SalesChannelContext $salesChannelContext,
        ShareBasketEntity $shareBasketEntity
    ): void {
        foreach ($shareBasketEntity->getLineItems() as $shareBasketLineItemEntity) {
            try {
                if ($shareBasketLineItemEntity->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                    $this->addProduct($cart, $salesChannelContext, $shareBasketLineItemEntity);
                }

                if ($shareBasketLineItemEntity->getType() === PromotionProcessor::LINE_ITEM_TYPE) {
                    $this->addPromotion($cart, $salesChannelContext, $shareBasketLineItemEntity);
                }
            } catch (\Exception $e) {
            }
        }
    }

    private function persistCart(Context $context, array $data, int $attempts = 0): ?string
    {
        if ($attempts > 3) {
            return null;
        }

        try {
            $result = $this->repository->create([$data], $context);
        } catch (\Exception $e) {
            $data['basketId'] = $this->generateShareBasketId();

            return $this->persistCart($context, $data, ++$attempts);
        }

        $event = $result->getEventByEntityName(ShareBasketDefinition::ENTITY_NAME);

        if ($event === null) {
            return null;
        }

        $data = $event->getPayloads()[0];
        $this->session->set('froshShareBasketHash', $data['hash']);

        return $this->generateBasketUrl($data['basketId']);
    }

    private function generateBasketUrl(string $basketId): string
    {
        return $this->router->generate(
            'frontend.frosh.share-basket.load',
            ['basketId' => $basketId],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    private function generateShareBasketId(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';
        $basketId = '';
        for ($i = 0; $i < 11; ++$i) {
            try {
                $basketId .= $characters[random_int(0, 63)];
            } catch (\Exception $e) {
            }
        }

        return $basketId;
    }

    private function traceErrors(Cart $cart): Cart
    {
        if ($cart->getErrors()->count() <= 0) {
            return $cart;
        }

        foreach ($cart->getErrors() as $error) {
            $type = 'danger';

            if ($error->getLevel() === Error::LEVEL_NOTICE) {
                $type = 'info';
            }

            $parameters = [];
            foreach ($error->getParameters() as $key => $value) {
                $parameters['%' . $key . '%'] = $value;
            }

            $message = $this->dataCollectorTranslator->trans('checkout.' . $error->getMessageKey(), $parameters);

            $this->session->getFlashBag()->add($type, $message);
        }

        $cart->getErrors()->clear();

        return $cart;
    }

    /**
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     * @throws LineItemNotStackableException
     * @throws MixedLineItemTypeException
     */
    private function addProduct(
        Cart $cart,
        SalesChannelContext $salesChannelContext,
        ShareBasketLineItemEntity $shareBasketLineItemEntity
    ): void {
        $productId = $this->getProductIdByNumber($shareBasketLineItemEntity->getIdentifier(), $salesChannelContext);

        if ($productId === null) {
            return;
        }

        $lineItem = new LineItem(
            $productId,
            $shareBasketLineItemEntity->getType(),
            $productId,
            $shareBasketLineItemEntity->getQuantity()
        );
        $lineItem->setStackable($shareBasketLineItemEntity->isStackable());
        $lineItem->setRemovable($shareBasketLineItemEntity->isRemovable());
        $lineItem->setPayload(['id' => $productId]);
        $this->cartService->add($cart, $lineItem, $salesChannelContext);
    }

    /**
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     * @throws LineItemNotStackableException
     * @throws MixedLineItemTypeException
     */
    private function addPromotion(
        Cart $cart,
        SalesChannelContext $salesChannelContext,
        ShareBasketLineItemEntity $shareBasketLineItemEntity
    ): void {
        $itemBuilder = new PromotionItemBuilder();
        $lineItem = $itemBuilder->buildPlaceholderItem(
            $shareBasketLineItemEntity->getIdentifier(),
            $salesChannelContext->getContext()->getCurrencyPrecision()
        );
        $this->cartService->add($cart, $lineItem, $salesChannelContext);
    }

    private function getProductIdByNumber(string $number, SalesChannelContext $salesChannelContext): ?string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('productNumber', $number));

        return $this->productRepository->searchIds($criteria, $salesChannelContext)->firstId();
    }
}
