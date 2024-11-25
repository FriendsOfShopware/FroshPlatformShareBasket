<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CustomerShareBasketServiceInterface
{
    /**
     * @return EntitySearchResult<ShareBasketCollection>|null
     */
    public function loadCustomerCarts(SalesChannelContext $salesChannelContext, int $page): ?EntitySearchResult;

    public function removeCustomerCart(string $id, SalesChannelContext $salesChannelContext): void;
}
