<?php declare(strict_types=1);

namespace Frosh\ShareBasket\ScheduledTask;

use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ShareBasketCleanupTaskHandler extends ScheduledTaskHandler
{
    /**
     * @var ShareBasketServiceInterface
     */
    private $shareBasketService;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ShareBasketServiceInterface $shareBasketService
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->shareBasketService = $shareBasketService;
    }

    public static function getHandledMessages(): iterable
    {
        return [
            ShareBasketCleanupTask::class,
        ];
    }

    public function run(): void
    {
        $this->shareBasketService->cleanup();
    }
}
