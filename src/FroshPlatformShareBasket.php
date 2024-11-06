<?php

declare(strict_types=1);

namespace Frosh\ShareBasket;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FroshPlatformShareBasket extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        if (!$this->container instanceof ContainerInterface) {
            return;
        }

        $connection = $this->container->get(Connection::class);
        if (!$connection instanceof Connection) {
            return;
        }

        $connection->executeStatement('DROP TABLE IF EXISTS `frosh_share_basket_line_item`');
        $connection->executeStatement('DROP TABLE IF EXISTS `frosh_share_basket`');
    }
}
