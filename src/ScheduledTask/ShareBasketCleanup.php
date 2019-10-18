<?php declare(strict_types=1);

namespace Frosh\ShareBasket\ScheduledTask;

use Shopware\Core\Framework\ScheduledTask\ScheduledTask;

class ShareBasketCleanup extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'frosh.share_basket_cleanup';
    }

    public static function getDefaultInterval(): int
    {
        return 60 * 60 * 24;
    }
}
