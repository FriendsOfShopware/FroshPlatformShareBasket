<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem\ShareBasketLineItemCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ShareBasketEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $basketId;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var int
     */
    protected $saveCount;

    /**
     * @var string
     */
    protected $salesChannelId;

    /**
     * @var SalesChannelEntity
     */
    protected $salesChannel;

    /**
     * @var ShareBasketLineItemCollection
     */
    protected $lineItems;

    /**
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface
     */
    protected $updatedAt;

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
