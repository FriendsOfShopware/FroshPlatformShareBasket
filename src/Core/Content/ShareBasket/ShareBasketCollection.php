<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ShareBasketEntity>
 */
class ShareBasketCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ShareBasketEntity::class;
    }
}
