<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CustomerShareBasketService implements CustomerShareBasketServiceInterface
{
    private const LIMIT = 10;

    public function __construct(
        private readonly EntityRepository $shareBasketRepository,
        private readonly EntityRepository $shareBasketCustomerRepository,
        private readonly SystemConfigService $systemConfigService
    ) {
    }


    public function loadCustomerCarts(SalesChannelContext $salesChannelContext, int $page): ?EntitySearchResult
    {
        $customerId = $salesChannelContext->getCustomerId();
        if ($customerId === null) {
            return null;
        }

        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('customers.id', $customerId))
            ->addAssociation('lineItems.product.cover')
            ->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING))
            ->setLimit(self::LIMIT)
            ->setOffset(($page - 1) * self::LIMIT)
            ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT)
        ;

        if ($this->systemConfigService->getBool('FroshPlatformShareBasket.config.showCartsFromAllSalesChannels', $salesChannelContext->getSalesChannelId()) === false) {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannelId()));
        }

        return $this->shareBasketRepository->search($criteria, $salesChannelContext->getContext());
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
