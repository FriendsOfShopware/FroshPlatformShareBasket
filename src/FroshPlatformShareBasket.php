<?php declare(strict_types=1);

namespace Frosh\ShareBasket;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class FroshPlatformShareBasket extends Plugin
{
    /**
     * @throws DBALException
     * @throws ConnectionException
     */
    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);
        $connection->beginTransaction();
        $connection->executeQuery('SET foreign_key_checks = 0;');
        $connection->executeQuery('DROP TABLE IF EXISTS `frosh_share_basket`');
        $connection->executeQuery('DROP TABLE IF EXISTS `frosh_share_basket_line_item`');
        $connection->executeQuery('SET foreign_key_checks = 1;');
        $connection->commit();
    }
}
