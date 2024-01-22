<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket\Events;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class ShareBasketPrepareLineItemEvent extends Event
{
    final public const EVENT_NAME = 'frosh.share_basket.prepare_line_item';

    /**
     * @param array{
     *     identifier: string,
     *     quantity: int,
     *     type: string,
     *     removable: bool,
     *     stackable: bool,
     *     payload: array<string, mixed>|null
     * } $shareBasketLineItem
     */
    public function __construct(
        private array $shareBasketLineItem,
        private readonly LineItem $lineItem,
        private readonly SalesChannelContext $salesChannelContext
    ) {}

    /**
     * @return array{
     *     identifier: string,
     *     quantity: int,
     *     type: string,
     *     removable: bool,
     *     stackable: bool,
     *     payload: array<string, mixed>|null
     * }
     */
    public function getShareBasketLineItem(): array
    {
        return $this->shareBasketLineItem;
    }

    /**
     * @param array{
     *     identifier: string,
     *     quantity: int,
     *     type: string,
     *     removable: bool,
     *     stackable: bool,
     *     payload: array<string, mixed>|null
     * } $shareBasketLineItem
     */
    public function setShareBasketLineItem(array $shareBasketLineItem): void
    {
        $this->shareBasketLineItem = $shareBasketLineItem;
    }

    public function getLineItem(): LineItem
    {
        return $this->lineItem;
    }

    /* @phpstan-ignore-next-line */
    private function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }
}
