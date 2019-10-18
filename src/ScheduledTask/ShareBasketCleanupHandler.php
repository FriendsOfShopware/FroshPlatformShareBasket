<?php declare(strict_types=1);

namespace Frosh\ShareBasket\ScheduledTask;

use Nette\Utils\DateTime;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\ScheduledTask\ScheduledTaskHandler;
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
        return [ShareBasketCleanup::class];
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function run(): void
    {
        $context = Context::createDefaultContext();
        $interval = -1 * abs($this->systemConfigService->get('ShareBasket.config.interval') ?: 6);

        $deleteBefore = new DateTime();
        $deleteBefore->modify($interval . ' months');

        $criteria = new Criteria();
        $criteria->addFilter(
            new RangeFilter('created_at', [
                'lt' => $deleteBefore->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ])
        );
        $criteria->addAssociation('lineItems');

        $shareBasketEntities = $this->repository->search($criteria, $context)->getIds();
        $this->repository->delete($shareBasketEntities, $context);
    }
}
