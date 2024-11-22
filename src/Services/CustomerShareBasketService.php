<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class CustomerShareBasketService implements CustomerShareBasketServiceInterface
{
    /**
     * @param EntityRepository<ShareBasketCollection> $shareBasketRepository
     * @param EntityRepository<EntityCollection<Entity>> $shareBasketCustomerRepository
     */
    public function __construct(
        #[Autowire(service: 'frosh_share_basket.repository')]
        private EntityRepository $shareBasketRepository,
        #[Autowire(service: 'frosh_share_basket_customer.repository')]
        private EntityRepository $shareBasketCustomerRepository,
        private readonly SystemConfigService $systemConfigService,
    ) {}

    public function loadCustomerCarts(SalesChannelContext $salesChannelContext): ?ShareBasketCollection
    {
        $customerId = $salesChannelContext->getCustomerId();
        if ($customerId === null) {
            return null;
        }

        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('customers.id', $customerId))
            ->addAssociation('lineItems.product.cover')
        ;

        if ($this->systemConfigService->getBool('FroshPlatformShareBasket.config.showCartsFromAllSalesChannels', $salesChannelContext->getSalesChannelId()) === false) {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannelId()));
        }

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
            ],
        ], $salesChannelContext->getContext());
    }
}
