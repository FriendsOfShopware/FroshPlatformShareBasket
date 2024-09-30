<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1727439408ShareBasketCustomer extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1727439408;
    }

    public function update(Connection $connection): void
    {
        $this->createShareBasketCustomerTable($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function createShareBasketCustomerTable(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE IF NOT EXISTS `frosh_share_basket_customer` (
                `share_basket_id` BINARY(16) NOT NULL,
                `customer_id` BINARY(16) NOT NULL,
                PRIMARY KEY (`share_basket_id`, `customer_id`),
                CONSTRAINT `fk.share_basket_customer.share_basket_id` FOREIGN KEY (`share_basket_id`) REFERENCES `frosh_share_basket` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk.share_basket_customer.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL;

        $connection->executeStatement($sql);
    }
}
