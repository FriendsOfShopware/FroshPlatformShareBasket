<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem\ShareBasketLineItemEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface CustomizedProductsServiceInterface
{
    public function prepareCustomizedProductsLineItem(LineItem $lineItem): ?array;

    public function addCustomProduct(
        Cart $cart,
        SalesChannelContext $salesChannelContext,
        ShareBasketLineItemEntity $shareBasketLineItemEntity,
        Request $request
    ): void;
}
