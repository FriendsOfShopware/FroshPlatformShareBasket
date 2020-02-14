<?php declare(strict_types=1);

namespace Frosh\ShareBasket\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ShareBasketCleanupTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'frosh.share_basket_cleanup';
    }

    public static function getDefaultInterval(): int
    {
        return 86400; // 1 day
    }
}
