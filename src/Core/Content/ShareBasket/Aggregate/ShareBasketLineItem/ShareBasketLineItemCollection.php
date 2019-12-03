<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(ShareBasketLineItemEntity $entity)
 * @method void                           set(string $key, ShareBasketLineItemEntity $entity)
 * @method ShareBasketLineItemEntity[]    getIterator()
 * @method ShareBasketLineItemEntity[]    getElements()
 * @method ShareBasketLineItemEntity|null get(string $key)
 * @method ShareBasketLineItemEntity|null first()
 * @method ShareBasketLineItemEntity|null last()
 */
class ShareBasketLineItemCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ShareBasketLineItemEntity::class;
    }
}
