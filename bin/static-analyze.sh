#!/usr/bin/env bash

php "`dirname \"$0\"`"/phpstan-config-generator.php
composer dump-autoload
php ../../../dev-ops/analyze/vendor/bin/phpstan analyze --configuration phpstan.neon --autoload-file=../../../vendor/autoload.php src
