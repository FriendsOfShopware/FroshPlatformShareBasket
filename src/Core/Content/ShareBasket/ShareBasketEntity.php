<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem\ShareBasketLineItemCollection;
use Shopware\Core\Checkout\Customer\CustomerCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ShareBasketEntity extends Entity
{
    use EntityIdTrait;

    protected string $basketId;

    protected string $hash;

    protected int $saveCount;

    protected string $salesChannelId;

    protected SalesChannelEntity $salesChannel;

    protected ?CustomerCollection $customers;

    protected ShareBasketLineItemCollection $lineItems;

    public function getBasketId(): string
    {
        return $this->basketId;
    }

    public function setBasketId(string $basketId): void
    {
        $this->basketId = $basketId;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getSaveCount(): int
    {
        return $this->saveCount;
    }

    public function setSaveCount(int $saveCount): void
    {
        $this->saveCount = $saveCount;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getSalesChannel(): SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    public function getCustomers(): ?CustomerCollection
    {
        return $this->customers;
    }

    public function setCustomers(CustomerCollection $customers): void
    {
        $this->customers = $customers;
    }

    public function getLineItems(): ShareBasketLineItemCollection
    {
        return $this->lineItems;
    }

    public function setLineItems(ShareBasketLineItemCollection $lineItems): void
    {
        $this->lineItems = $lineItems;
    }

    public function increaseSaveCount(): int
    {
        return ++$this->saveCount;
    }
}
