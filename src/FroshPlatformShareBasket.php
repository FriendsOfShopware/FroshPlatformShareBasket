<?php declare(strict_types=1);

namespace Frosh\ShareBasket;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class FroshPlatformShareBasket extends Plugin
{
    public function install(InstallContext $context): void
    {
        $this->setDefaults([
            'interval' => 6,
            'email' => true,
            'facebook' => true,
            'whatsapp' => true,
            'webshare' => true,
        ]);
    }

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

    public function setDefaults(array $data): void
    {
        /** @var SystemConfigService $systemConfig */
        $systemConfig = $this->container->get(SystemConfigService::class);
        $domain = $this->getName() . '.config.';

        foreach ($data as $key => $value) {
            $defaultValue = $systemConfig->get($domain . $key);
            if ($defaultValue === null) {
                $systemConfig->set($domain . $key, $value);
            }
        }
    }
}
