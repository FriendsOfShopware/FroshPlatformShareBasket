<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketCustomer;

use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class ShareBasketCustomerDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'frosh_share_basket_customer';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('share_basket_id', 'shareBasketId', ShareBasketDefinition::class))
                ->addFlags(new PrimaryKey(), new Required()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))
                ->addFlags(new PrimaryKey(), new Required()),

            new ManyToOneAssociationField('shareBasket', 'share_basket_id', ShareBasketDefinition::class),
            new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class),
        ]);
    }
}
