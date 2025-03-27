<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket\Events;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem\ShareBasketLineItemEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class ShareBasketAddLineItemEvent extends Event
{
    final public const EVENT_NAME = 'frosh.share_basket.add_line_item';

    public function __construct(
        private readonly Cart $cart,
        private readonly SalesChannelContext $salesChannelContext,
        private readonly ShareBasketLineItemEntity $shareBasketLineItemEntity,
    ) {
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getShareBasketLineItemEntity(): ShareBasketLineItemEntity
    {
        return $this->shareBasketLineItemEntity;
    }
}
