<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1679938988ShareBasketLineItem extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1679938988;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            ALTER TABLE `frosh_share_basket_line_item` CHANGE `cart_id` `share_basket_id` BINARY(16) NOT NULL;
            ALTER TABLE `frosh_share_basket_line_item` ADD COLUMN `payload` JSON NULL;
        SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
