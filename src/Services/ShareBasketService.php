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
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
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
     * @var SalesChannelContext
     */
    private $context;

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
        DataCollectorTranslator $dataCollectorTranslator
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->cartService = $cartService;
        $this->repository = $repository;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->dataCollectorTranslator = $dataCollectorTranslator;
    }

    /**
     * @return bool|string
     */
    public function saveCart(SalesChannelContext $context)
    {
        $this->context = $context;
        $master = $this->requestStack->getMasterRequest();

        if ($master === null) {
            return false;
        }

        if ($master->getSession() === null) {
            return false;
        }

        $this->session = $master->getSession();
        $data = $this->prepareLineItems($this->context);

        try {
            $criteria = new Criteria();
        } catch (InconsistentCriteriaIdsException $e) {
            return false;
        }

        $criteria->addFilter(new EqualsFilter('salesChannelId', $this->context->getSalesChannel()->getId()));
        $criteria->addFilter(new EqualsFilter('hash', $data['hash']));

        /** @var ShareBasketEntity $shareBasketEntity */
        $shareBasketEntity = $this->repository->search($criteria, $this->context->getContext())->first();

        if ($shareBasketEntity instanceof ShareBasketEntity) {
            $data['id'] = $shareBasketEntity->getId();
            $data['basketId'] = $shareBasketEntity->getBasketId();
            if ($this->session->get('froshShareBasketHash') !== $data['hash']) {
                $data['saveCount'] = $shareBasketEntity->increaseSaveCount();
            }

            unset($data['lineItems']);
            $this->repository->update([$data], $this->context->getContext());

            return $this->generateBasketUrl($data['basketId']);
        }

        return $this->persistCart($data);
    }

    /**
     * @throws \Exception
     *
     * @return bool|Cart
     */
    public function loadCart(SalesChannelContext $context)
    {
        $this->context = $context;
        $master = $this->requestStack->getMasterRequest();

        if ($master === null) {
            return false;
        }

        if ($master->getSession() === null) {
            return false;
        }

        if ($master->get('basketId') === null) {
            return false;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $context->getSalesChannel()->getId()));
        $criteria->addFilter(new EqualsFilter('basketId', $master->get('basketId')));
        $criteria->addAssociation('lineItems');

        /** @var ShareBasketEntity|null $shareBasketEntity */
        $shareBasketEntity = $this->repository->search($criteria, $context->getContext())->first();

        if (!$shareBasketEntity instanceof ShareBasketEntity) {
            return false;
        }

        $this->session = $master->getSession();
        $this->session->set('froshShareBasketHash', $shareBasketEntity->getHash());

        $token = $master->request->getAlnum('token', $context->getToken());
        $name = $master->request->getAlnum('name', CartService::SALES_CHANNEL);

        $this->cartService->createNew($token, $name);
        $cart = $this->cartService->getCart($context->getToken(), $context);
        $cart->addLineItems($this->collectLineItems($shareBasketEntity));

        return $this->traceErrors($this->cartService->recalculate($cart, $context));
    }

    public function prepareLineItems(SalesChannelContext $context): array
    {
        $cart = $this->cartService->getCart($context->getToken(), $context);

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
            'salesChannelId' => $context->getSalesChannel()->getId(),
            'lineItems' => $lineItems,
        ];

        return $data;
    }

    /**
     * @throws \Exception
     */
    private function collectLineItems(ShareBasketEntity $shareBasketEntity): LineItemCollection
    {
        $collection = new LineItemCollection();
        foreach ($shareBasketEntity->getLineItems() as $shareBasketLineItemEntity) {
            if ($shareBasketLineItemEntity->getType() === PromotionProcessor::LINE_ITEM_TYPE) {
                $itemBuilder = new PromotionItemBuilder();
                $lineItem = $itemBuilder->buildPlaceholderItem(
                    $shareBasketLineItemEntity->getIdentifier(),
                    $this->context->getContext()->getCurrencyPrecision()
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
    private function persistCart(array $data, int $attempts = 0)
    {
        if ($attempts > 3) {
            return false;
        }

        try {
            /** @var EntityWrittenContainerEvent $result */
            $result = $this->repository->create([$data], $this->context->getContext());
        } catch (\Exception $e) {
            $data['basketId'] = $this->generateShareBasketId();

            return $this->persistCart($data, ++$attempts);
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
