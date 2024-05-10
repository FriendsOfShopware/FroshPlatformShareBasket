<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\ScheduledTask;

use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: ShareBasketCleanupTask::class)]
class ShareBasketCleanupTaskHandler extends ScheduledTaskHandler
{
    /**
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly ShareBasketServiceInterface $shareBasketService,
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public function run(): void
    {
        $this->shareBasketService->cleanup();
    }
}
