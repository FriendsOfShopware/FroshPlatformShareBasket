<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\Customer;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketCustomer\ShareBasketCustomerDefinition;
use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CustomerExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return CustomerDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField('froshSavedCarts', ShareBasketDefinition::class, ShareBasketCustomerDefinition::class, 'customer_id', 'share_basket_id'))
        );
    }
}
