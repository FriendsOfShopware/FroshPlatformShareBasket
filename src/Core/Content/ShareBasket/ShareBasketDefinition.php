<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketCustomer\ShareBasketCustomerDefinition;
use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem\ShareBasketLineItemDefinition;
use PxswTheme\WurmB2BSuiteBundle\Core\Content\WurmB2BSuite\B2BSuiteSalesAgent\Aggregate\CustomerSalesAgent\CustomerSalesAgentDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class ShareBasketDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'frosh_share_basket';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ShareBasketCollection::class;
    }

    public function getEntityClass(): string
    {
        return ShareBasketEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'saveCount' => 1,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new StringField('basket_id', 'basketId'),
            new IntField('save_count', 'saveCount'),
            new StringField('hash', 'hash'),
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new Required()),

            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id'),
            (new OneToManyAssociationField('lineItems', ShareBasketLineItemDefinition::class, 'share_basket_id'))->addFlags(new CascadeDelete()),
            (new ManyToManyAssociationField('customers', CustomerDefinition::class, ShareBasketCustomerDefinition::class, 'share_basket_id', 'customer_id')),
        ]);
    }
}
