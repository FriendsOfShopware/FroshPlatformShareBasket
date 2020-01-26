<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface ShareBasketServiceInterface
{
    /**
     * @return bool|string
     */
    public function saveCart(SalesChannelContext $context);

    /**
     * @return bool|Cart
     */
    public function loadCart(Request $request, SalesChannelContext $context);

    public function prepareLineItems(SalesChannelContext $context): array;
}
