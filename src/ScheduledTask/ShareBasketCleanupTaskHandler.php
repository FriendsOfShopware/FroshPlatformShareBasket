<?php declare(strict_types=1);

namespace Frosh\ShareBasket\ScheduledTask;

use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: ShareBasketCleanupTask::class)]
class ShareBasketCleanupTaskHandler
{
    public function __construct(
        private readonly ShareBasketServiceInterface $shareBasketService
    ) {
    }

    public function __invoke(ShareBasketCleanupTask $basketCleanupTask): void
    {
        $this->shareBasketService->cleanup();
    }
}
