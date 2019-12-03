<?php declare(strict_types=1);

namespace Frosh\ShareBasket\ScheduledTask;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ShareBasketCleanupHandler extends ScheduledTaskHandler
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EntityRepositoryInterface
     */
    private $repository;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SystemConfigService $systemConfigService,
        EntityRepositoryInterface $repository
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->systemConfigService = $systemConfigService;
        $this->repository = $repository;
    }

    public static function getHandledMessages(): iterable
    {
        return [
            ShareBasketCleanup::class,
        ];
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function run(): void
    {
        $interval = -1 * abs($this->systemConfigService->get('ShareBasket.config.interval') ?: 6);
        $dateTime = (new \DateTime())->add(\DateInterval::createFromDateString($interval . ' months'));

        $criteria = new Criteria();
        $criteria->addFilter(new RangeFilter(
            'createdAt',
            [
                RangeFilter::LTE => $dateTime->format(DATE_ATOM),
            ]
        ));

        $criteria->addAssociation('lineItems');

        $shareBasketEntities = $this->repository->search($criteria, Context::createDefaultContext());

        if (empty($shareBasketEntities->getIds())) {
            return;
        }

        $shareBasketEntitiesIds = array_map(function ($id) {return ['id' => $id]; }, $shareBasketEntities->getIds());

        $this->repository->delete($shareBasketEntitiesIds, Context::createDefaultContext());
    }
}
