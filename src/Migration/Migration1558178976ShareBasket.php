<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1558178976ShareBasket extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1558178976;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `frosh_share_basket` (
    `id` BINARY(16) NOT NULL,
    `basket_id` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `hash` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `save_count` INT(11) DEFAULT 0,
    `sales_channel_id` BINARY(16) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`),
    UNIQUE KEY `hash` (`hash`),
    UNIQUE KEY `basketId` (`basket_id`),
    CONSTRAINT `fk.frosh_share_basket.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
        REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
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
