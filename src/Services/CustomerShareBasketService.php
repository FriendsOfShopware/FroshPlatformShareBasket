<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomerShareBasketService implements CustomerShareBasketServiceInterface
{
    public function __construct(
        private readonly EntityRepository $shareBasketRepository,
        private readonly EntityRepository $shareBasketCustomerRepository,
    ) {
    }

    public function loadCustomerCarts(SalesChannelContext $salesChannelContext): ?ShareBasketCollection
    {
        $customerId = $salesChannelContext->getCustomerId();
        if ($customerId === null) {
            return null;
        }

        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannelId()))
            ->addFilter(new EqualsFilter('customers.id', $customerId))
            ->addAssociation('lineItems.product.cover')
        ;

        return $this->shareBasketRepository->search($criteria, $salesChannelContext->getContext())->getEntities();
    }

    public function removeCustomerCart(string $id, SalesChannelContext $salesChannelContext): void
    {
        $customerId = $salesChannelContext->getCustomerId();
        if ($customerId === null) {
            return;
        }

        $this->shareBasketCustomerRepository->delete([
            [
                'shareBasketId' => $id,
                'customerId' => $customerId,
            ]
        ], $salesChannelContext->getContext());
    }
}
