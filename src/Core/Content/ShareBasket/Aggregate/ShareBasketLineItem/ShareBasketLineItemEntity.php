<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem;

use Frosh\ShareBasket\Core\Content\ShareBasket\ShareBasketEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ShareBasketLineItemEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $identifier;

    protected int $quantity;

    protected string $type;

    protected string $shareBasketId;

    protected ShareBasketEntity $shareBasket;

    protected bool $removable = false;

    protected bool $stackable = false;

    protected ProductEntity $product;

    protected ?array $payload = null;

    protected string $lineItemIdentifier;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getShareBasketId(): string
    {
        return $this->shareBasketId;
    }

    public function setShareBasketId(string $shareBasketId): void
    {
        $this->shareBasketId = $shareBasketId;
    }

    public function getShareBasket(): ShareBasketEntity
    {
        return $this->shareBasket;
    }

    public function setShareBasket(ShareBasketEntity $shareBasket): void
    {
        $this->shareBasket = $shareBasket;
    }

    public function isRemovable(): bool
    {
        return $this->removable;
    }

    public function setRemovable(bool $removable): void
    {
        $this->removable = $removable;
    }

    public function isStackable(): bool
    {
        return $this->stackable;
    }

    public function setStackable(bool $stackable): void
    {
        $this->stackable = $stackable;
    }

    public function getProduct(): ProductEntity
    {
        return $this->product;
    }

    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): void
    {
        $this->payload = $payload;
    }

    public function getLineItemIdentifier(): string
    {
        return $this->lineItemIdentifier;
    }

    public function setLineItemIdentifier(string $lineItemIdentifier): void
    {
        $this->lineItemIdentifier = $lineItemIdentifier;
    }
}
