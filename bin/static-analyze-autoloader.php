<?php

declare(strict_types=1);

use Shopware\Core\Framework\Adapter\Database\MySQLFactory;
use staabm\PHPStanDba\QueryReflection\PdoQueryReflector;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use staabm\PHPStanDba\QueryReflection\RuntimeConfiguration;
use Symfony\Component\Dotenv\Dotenv;

$projectRoot = dirname(__DIR__, 4);
require_once $projectRoot . '/vendor/autoload.php';

$swagCustomizedProductsFound = false;
$swagCustomizedProductsPath = 'custom/plugins/SwagCustomizedProducts';

if (\file_exists($projectRoot . '/' . $swagCustomizedProductsPath . '/src/SwagCustomizedProducts.php')) {
    $swagCustomizedProductsFound = true;
    $pathToCustomizedProducts = $projectRoot . '/' . $swagCustomizedProductsPath . '/vendor/autoload.php';
    if (\file_exists($pathToCustomizedProducts)) {
        require_once $pathToCustomizedProducts;
    } else {
        echo "Please execute 'composer dump-autoload' in your SwagCustomizedProducts directory\n";
    }
}

if (!$swagCustomizedProductsFound) {
    echo "You need the SwagCustomizedProducts plugin for static analyze to work.\n";
}

if (file_exists($projectRoot . '/.env')) {
    (new Dotenv())->usePutEnv()->load($projectRoot . '/.env');
}

if (class_exists(QueryReflection::class)) {
    $config = new RuntimeConfiguration();
    $config->stringifyTypes(true);

    /** @var \PDO $pdo */
    $pdo = MySQLFactory::create()->getWrappedConnection();
    QueryReflection::setupReflector(
        new PdoQueryReflector($pdo),
        $config
    );
}
