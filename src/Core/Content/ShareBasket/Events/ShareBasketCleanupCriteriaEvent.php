<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket\Events;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Contracts\EventDispatcher\Event;

class ShareBasketCleanupCriteriaEvent extends Event
{
    public function __construct(
        private readonly Criteria $criteria,
    ) {}

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }
}
