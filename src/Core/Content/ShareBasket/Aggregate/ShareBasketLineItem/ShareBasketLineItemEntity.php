<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ShareBasketLineItemEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;

    protected string $identifier;

    protected int $quantity;

    protected string $type;

    protected string $cartId;

    protected Cart $cart;

    protected bool $removable = false;

    protected bool $stackable = false;

    protected ProductEntity $product;

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

    public function getCartId(): string
    {
        return $this->cartId;
    }

    public function setCartId(string $cartId): void
    {
        $this->cartId = $cartId;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): void
    {
        $this->cart = $cart;
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
}
