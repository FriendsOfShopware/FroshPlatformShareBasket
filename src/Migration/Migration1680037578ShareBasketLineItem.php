<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1680037578ShareBasketLineItem extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1680037578;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
                ALTER TABLE `frosh_share_basket_line_item` ADD COLUMN `line_item_identifier` VARCHAR(255) NOT NULL;
            SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
