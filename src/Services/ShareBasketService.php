<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketDefinition;
use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\DataCollectorTranslator;

class ShareBasketService implements ShareBasketServiceInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var EntityRepositoryInterface
     */
    private $repository;

    /**
     * @var RequestStack
     */
    private $requestStack;

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

    public function __construct(
        SystemConfigService $systemConfigService,
        CartService $cartService,
        EntityRepositoryInterface $repository,
        RequestStack $requestStack,
        RouterInterface $router,
        Session $session,
        DataCollectorTranslator $dataCollectorTranslator
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->cartService = $cartService;
        $this->repository = $repository;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->session = $session;
        $this->dataCollectorTranslator = $dataCollectorTranslator;
    }

    /**
     * @return bool|string
     */
    public function saveCart(SalesChannelContext $salesChannelContext)
    {
        $data = $this->prepareLineItems($salesChannelContext);

        try {
            $criteria = new Criteria();
        } catch (InconsistentCriteriaIdsException $e) {
            return false;
        }

        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannel()->getId()));
        $criteria->addFilter(new EqualsFilter('hash', $data['hash']));

        /** @var ShareBasketEntity $shareBasketEntity */
        $shareBasketEntity = $this->repository->search($criteria, $salesChannelContext->getContext())->first();

        if ($shareBasketEntity instanceof ShareBasketEntity) {
            $data['id'] = $shareBasketEntity->getId();
            $data['basketId'] = $shareBasketEntity->getBasketId();
            if ($this->session->get('froshShareBasketHash') !== $data['hash']) {
                $data['saveCount'] = $shareBasketEntity->increaseSaveCount();
            }

            unset($data['lineItems']);
            $this->repository->update([$data], $salesChannelContext->getContext());

            return $this->generateBasketUrl($data['basketId']);
        }

        return $this->persistCart($salesChannelContext->getContext(), $data);
    }

    /**
     * @throws \Exception
     *
     * @return bool|Cart
     */
    public function loadCart(SalesChannelContext $salesChannelContext)
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        if ($currentRequest === null) {
            return false;
        }

        if ($currentRequest->get('basketId') === null) {
            return false;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannel()->getId()));
        $criteria->addFilter(new EqualsFilter('basketId', $currentRequest->get('basketId')));
        $criteria->addAssociation('lineItems');

        /** @var ShareBasketEntity|null $shareBasketEntity */
        $shareBasketEntity = $this->repository->search($criteria, $salesChannelContext->getContext())->first();

        if (!$shareBasketEntity instanceof ShareBasketEntity) {
            return false;
        }

        $this->session->set('froshShareBasketHash', $shareBasketEntity->getHash());

        $token = $currentRequest->request->getAlnum('token', $salesChannelContext->getToken());
        $name = $currentRequest->request->getAlnum('name', CartService::SALES_CHANNEL);

        $this->cartService->createNew($token, $name);
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
        $cart->addLineItems($this->collectLineItems($salesChannelContext, $shareBasketEntity));

        return $this->traceErrors($this->cartService->recalculate($cart, $salesChannelContext));
    }

    public function prepareLineItems(SalesChannelContext $salesChannelContext): array
    {
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        $lineItems = [];
        foreach ($cart->getLineItems() as $lineItem) {
            $identifier = $lineItem->getId();
            if ($lineItem->hasPayloadValue('code')) {
                $identifier = $lineItem->getReferencedId();
            }
            $lineItems[] = [
                'identifier' => $identifier,
                'quantity' => $lineItem->getQuantity(),
                'type' => $lineItem->getType(),
                'removable' => $lineItem->isRemovable(),
                'stackable' => $lineItem->isStackable(),
            ];
        }

        $data = [
            'basketId' => $this->generateShareBasketId(),
            'hash' => sha1(serialize($lineItems)),
            'salesChannelId' => $salesChannelContext->getSalesChannel()->getId(),
            'lineItems' => $lineItems,
        ];

        return $data;
    }

    /**
     * @throws \Exception
     */
    private function collectLineItems(SalesChannelContext $salesChannelContext, ShareBasketEntity $shareBasketEntity): LineItemCollection
    {
        $collection = new LineItemCollection();
        foreach ($shareBasketEntity->getLineItems() as $shareBasketLineItemEntity) {
            if ($shareBasketLineItemEntity->getType() === PromotionProcessor::LINE_ITEM_TYPE) {
                $itemBuilder = new PromotionItemBuilder();
                $lineItem = $itemBuilder->buildPlaceholderItem(
                    $shareBasketLineItemEntity->getIdentifier(),
                    $salesChannelContext->getContext()->getCurrencyPrecision()
                );
            } else {
                $lineItem = new LineItem(
                    $shareBasketLineItemEntity->getIdentifier(),
                    $shareBasketLineItemEntity->getType(),
                    $shareBasketLineItemEntity->getIdentifier(),
                    $shareBasketLineItemEntity->getQuantity()
                );
                $lineItem->setStackable($shareBasketLineItemEntity->isStackable());
                $lineItem->setRemovable($shareBasketLineItemEntity->isRemovable());
                $lineItem->setPayload(['id' => $shareBasketLineItemEntity->getIdentifier()]);
            }

            $collection->add($lineItem);
        }

        return $collection;
    }

    /**
     * @return bool|string
     */
    private function persistCart(Context $context, array $data, int $attempts = 0)
    {
        if ($attempts > 3) {
            return false;
        }

        try {
            $result = $this->repository->create([$data], $context);
        } catch (\Exception $e) {
            $data['basketId'] = $this->generateShareBasketId();

            return $this->persistCart($context, $data, ++$attempts);
        }

        $event = $result->getEventByEntityName(ShareBasketDefinition::ENTITY_NAME);

        if ($event === null) {
            return false;
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
}
