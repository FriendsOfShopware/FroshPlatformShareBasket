<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface ShareBasketServiceInterface
{
    /**
     * @return bool|string
     */
    public function saveCart(SalesChannelContext $context);

    /**
     * @return bool|Cart
     */
    public function loadCart(SalesChannelContext $context);

    public function prepareLineItems(SalesChannelContext $context): array;
}
