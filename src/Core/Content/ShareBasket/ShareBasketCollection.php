<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(ShareBasketEntity $entity)
 * @method void                   set(string $key, ShareBasketEntity $entity)
 * @method ShareBasketEntity[]    getIterator()
 * @method ShareBasketEntity[]    getElements()
 * @method ShareBasketEntity|null get(string $key)
 * @method ShareBasketEntity|null first()
 * @method ShareBasketEntity|null last()
 */
class ShareBasketCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ShareBasketEntity::class;
    }
}
