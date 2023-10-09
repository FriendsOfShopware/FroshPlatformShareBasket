<?php
declare(strict_types=1);

namespace Frosh\ShareBasket;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class FroshPlatformShareBasket extends Plugin
{
    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);
        $connection->executeStatement('SET foreign_key_checks = 0;');
        $connection->executeStatement('DROP TABLE IF EXISTS `frosh_share_basket`');
        $connection->executeStatement('DROP TABLE IF EXISTS `frosh_share_basket_line_item`');
        $connection->executeStatement('SET foreign_key_checks = 1;');
    }
}
