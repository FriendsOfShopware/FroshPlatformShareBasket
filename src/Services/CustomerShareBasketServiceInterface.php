<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CustomerShareBasketServiceInterface
{
    public function loadCustomerCarts(SalesChannelContext $salesChannelContext): ?ShareBasketCollection;

    public function removeCustomerCart(string $id, SalesChannelContext $salesChannelContext): void;
}
