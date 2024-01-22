<?php

declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\Product;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem\ShareBasketLineItemDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('shopware.entity.extension')]
class ProductShareBasketExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToOneAssociationField(
                'shareBasket',
                'product_number',
                'identifier',
                ShareBasketLineItemDefinition::class,
                false
            ),
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
