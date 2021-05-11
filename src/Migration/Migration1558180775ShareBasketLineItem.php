<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1558180775ShareBasketLineItem extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1558180775;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `frosh_share_basket_line_item` (
    `id` BINARY(16) NOT NULL,
    `cart_id` BINARY(16) NOT NULL,
    `identifier` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `type` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `quantity` INT(11) NOT NULL,
    `custom_fields` JSON NULL,
    `removable` TINYINT(1) NOT NULL DEFAULT 0,
    `stackable` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`),
    CONSTRAINT `json.frosh_share_basket_line_item.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
    CONSTRAINT `fk.frosh_share_basket_line_item.cart_id` FOREIGN KEY (`cart_id`)
        REFERENCES `frosh_share_basket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;
        $connection->executeUpdate($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
