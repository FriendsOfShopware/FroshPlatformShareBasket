<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface ShareBasketServiceInterface
{
    /**
     * @param array<mixed> $data
     */
    public function saveCart(Request $request, array $data, SalesChannelContext $salesChannelContext): ?string;

    public function loadCart(Request $request, SalesChannelContext $salesChannelContext): ?Cart;

    /**
     * @return array<mixed>
     */
    public function prepareLineItems(SalesChannelContext $salesChannelContext): array;

    public function cleanup(): ?EntityWrittenContainerEvent;
}
