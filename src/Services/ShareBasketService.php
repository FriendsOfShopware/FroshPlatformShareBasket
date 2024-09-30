<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem\ShareBasketLineItemEntity;
use Frosh\ShareBasket\Core\Content\ShareBasket\Events\ShareBasketAddLineItemEvent;
use Frosh\ShareBasket\Core\Content\ShareBasket\Events\ShareBasketCleanupCriteriaEvent;
use Frosh\ShareBasket\Core\Content\ShareBasket\Events\ShareBasketPrepareLineItemEvent;
use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketCollection;
use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketDefinition;
use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ShareBasketService implements ShareBasketServiceInterface
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly EntityRepository $shareBasketRepository,
        private readonly RouterInterface $router,
        private readonly SalesChannelRepository $productRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityRepository $shareBasketCustomerRepository,
    ) {
    }

    public function saveCart(Request $request, array $data, SalesChannelContext $salesChannelContext): ?string
    {
        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannelId()))
            ->addFilter(new EqualsFilter('hash', $data['hash']))
        ;

        $shareBasketEntity = $this->shareBasketRepository->search($criteria, $salesChannelContext->getContext())->first();
        if ($shareBasketEntity instanceof ShareBasketEntity) {
            $data['id'] = $shareBasketEntity->getId();
            $data['basketId'] = $shareBasketEntity->getBasketId();
            if ($request->getSession()->get('froshShareBasketHash') !== $data['hash']) {
                $data['saveCount'] = $shareBasketEntity->increaseSaveCount();
            }

            unset($data['lineItems']);
            $this->shareBasketRepository->update([$data], $salesChannelContext->getContext());
            $request->getSession()->set('froshShareBasketHash', $data['hash']);

            $this->persistSavedCartToCustomer($shareBasketEntity->getId(), $salesChannelContext);

            return $this->generateBasketUrl($data['basketId']);
        }

        return $this->persistCart($request, $salesChannelContext, $data);
    }

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
        $shareBasketEntity = $this->shareBasketRepository->search($criteria, $salesChannelContext->getContext())->first();

        if (!$shareBasketEntity instanceof ShareBasketEntity) {
            throw new \RuntimeException(sprintf('Could not found a shared basket with id %s', $basketId));
        }

        $request->getSession()->set('froshShareBasketHash', $shareBasketEntity->getHash());

        $token = $request->request->getAlnum('token', $salesChannelContext->getToken());

        $this->cartService->createNew($token);
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        $this->addLineItems($cart, $salesChannelContext, $shareBasketEntity);

        $restoredCart = $this->cartService->recalculate($cart, $salesChannelContext);
        $restoredCart->addErrors(...array_values($cart->getErrors()->getPersistent()->getElements()));

        return $restoredCart;
    }

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

            $shareBasketLineItem = [
                'identifier' => $identifier,
                'lineItemIdentifier' => $lineItem->getId(),
                'quantity' => $lineItem->getQuantity(),
                'type' => $lineItem->getType(),
                'removable' => $lineItem->isRemovable(),
                'stackable' => $lineItem->isStackable(),
                'payload' => null,
            ];

            $event = $this->eventDispatcher->dispatch(
                new ShareBasketPrepareLineItemEvent($shareBasketLineItem, $lineItem, $salesChannelContext),
                ShareBasketPrepareLineItemEvent::class,
            );

            $shareBasketLineItem = $event->getShareBasketLineItem();

            if (!$shareBasketLineItem['identifier']) {
                continue;
            }

            $lineItems[] = $shareBasketLineItem;
        }

        usort($lineItems, static fn (array $a, array $b): int => strcmp((string) $a['identifier'], (string) $b['identifier']));

        return [
            'basketId' => $this->generateShareBasketId(),
            'hash' => sha1(serialize($lineItems) . $salesChannelContext->getSalesChannelId()),
            'salesChannelId' => $salesChannelContext->getSalesChannel()->getId(),
            'lineItems' => $lineItems,
        ];
    }

    public function cleanup(): ?EntityWrittenContainerEvent
    {
        $months = (int) $this->systemConfigService->get('ShareBasket.config.interval') ?: 6;
        $dateTime = new \DateTime();
        $dateTime->modify(sprintf('-%s months', $months));

        $criteria = new Criteria();
        $criteria->addFilter(new RangeFilter(
            'createdAt',
            [
                RangeFilter::LTE => $dateTime->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        ));

        $criteria->addAssociation('lineItems');

        $this->eventDispatcher->dispatch(
            new ShareBasketCleanupCriteriaEvent($criteria),
            ShareBasketCleanupCriteriaEvent::class
        );

        $shareBasketEntities = $this->shareBasketRepository->searchIds($criteria, Context::createDefaultContext());

        if (empty($ids = $shareBasketEntities->getIds())) {
            return null;
        }

        $ids = array_map(static fn ($id) => ['id' => $id], $ids);

        return $this->shareBasketRepository->delete($ids, Context::createDefaultContext());
    }

    private function addLineItems(
        Cart $cart,
        SalesChannelContext $salesChannelContext,
        ShareBasketEntity $shareBasketEntity
    ): void {
        foreach ($shareBasketEntity->getLineItems() as $shareBasketLineItemEntity) {
            try {
                $this->eventDispatcher->dispatch(
                    new ShareBasketAddLineItemEvent($cart, $salesChannelContext, $shareBasketLineItemEntity),
                    ShareBasketAddLineItemEvent::class,
                );

                if ($shareBasketLineItemEntity->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                    $this->addProduct($cart, $salesChannelContext, $shareBasketLineItemEntity);
                }

                if ($shareBasketLineItemEntity->getType() === PromotionProcessor::LINE_ITEM_TYPE) {
                    $this->addPromotion($cart, $salesChannelContext, $shareBasketLineItemEntity);
                }
            } catch (\Exception) {
            }
        }
    }

    private function persistCart(Request $request, SalesChannelContext $salesChannelContext, array $data, int $attempts = 0): ?string
    {
        if ($attempts > 3) {
            return null;
        }

        try {
            $id = Uuid::randomHex();
            $data['id'] = $id;
            $result = $this->shareBasketRepository->create([$data], $salesChannelContext->getContext());

            $this->persistSavedCartToCustomer($id, $salesChannelContext);

        } catch (\Exception) {
            $data['basketId'] = $this->generateShareBasketId();

            return $this->persistCart($request, $salesChannelContext, $data, ++$attempts);
        }

        $event = $result->getEventByEntityName(ShareBasketDefinition::ENTITY_NAME);

        if (!$event instanceof EntityWrittenEvent) {
            return null;
        }

        $data = $event->getPayloads()[0];
        $request->getSession()->set('froshShareBasketHash', $data['hash']);

        return $this->generateBasketUrl($data['basketId']);
    }

    private function persistSavedCartToCustomer(string $shareBasketId, SalesChannelContext $salesChannelContext): void
    {
        $customerId = $salesChannelContext->getCustomerId();
        if ($customerId === null) {
            return;
        }

        $this->shareBasketCustomerRepository->upsert([[
            'shareBasketId' => $shareBasketId,
            'customerId' => $customerId,
        ]], $salesChannelContext->getContext());
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
            } catch (\Exception) {
            }
        }

        return $basketId;
    }

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
            $shareBasketLineItemEntity->getLineItemIdentifier(),
            $shareBasketLineItemEntity->getType(),
            $productId,
            $shareBasketLineItemEntity->getQuantity()
        );
        $this->setPayloadValues($shareBasketLineItemEntity->getPayload() ?? [], $lineItem);
        $lineItem->setStackable($shareBasketLineItemEntity->isStackable());
        $lineItem->setRemovable($shareBasketLineItemEntity->isRemovable());
        $lineItem->setPayload(['id' => $productId]);
        $this->cartService->add($cart, $lineItem, $salesChannelContext);
    }

    private function addPromotion(
        Cart $cart,
        SalesChannelContext $salesChannelContext,
        ShareBasketLineItemEntity $shareBasketLineItemEntity
    ): void {
        $itemBuilder = new PromotionItemBuilder();
        $lineItem = $itemBuilder->buildPlaceholderItem(
            $shareBasketLineItemEntity->getIdentifier()
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

    private function setPayloadValues(array $payload, LineItem $lineItem): void
    {
        foreach ($payload as $key => $value) {
            try {
                $lineItem->setPayloadValue($key, $value);
            } catch (\Exception) {
                // do nothing
            }
        }
    }
}
