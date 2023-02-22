<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ShareBasketLineItemEntity>
 */
class ShareBasketLineItemCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ShareBasketLineItemEntity::class;
    }
}
