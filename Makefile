.DEFAULT_GOAL := help

## Variable definition
PLUGIN_ROOT=$(shell cd -P -- '$(shell dirname -- "$0")' && pwd -P)
PROJECT_ROOT=$(PLUGIN_ROOT)/../../..
ifneq ("$(wildcard $(PROJECT_ROOT)/platform)", "")
    PLATFORM_ROOT=$(PROJECT_ROOT)/platform
else
	PLATFORM_ROOT=$(PROJECT_ROOT)
endif

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help

ecs-fix: ## Run easy coding standard on php
	@php $(PLATFORM_ROOT)/vendor/bin/ecs check --fix --config=$(PLATFORM_ROOT)/ecs.php src
.PHONY: ecs-fix

phpstan: ## Run phpstan
	@composer dump-autoload
	@php $(PLUGIN_ROOT)/bin/phpstan-config-generator.php
	@php $(PLATFORM_ROOT)/vendor/bin/phpstan analyze --configuration $(PLUGIN_ROOT)/phpstan.neon src
.PHONY: phpstan

psalm: ## Run psalm
	@cd $(PLATFORM_ROOT); php vendor/bin/psalm --config=$(PLUGIN_ROOT)/psalm.xml $(PLUGIN_ROOT)/src --diff --threads=4
.PHONY: psalm

administration-fix: ## Run eslint on the administration files
	$(PLATFORM_ROOT)/src/Administration/Resources/app/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config $(PLATFORM_ROOT)/src/Administration/Resources/app/administration/.eslintrc.js --ext .js,.vue --fix src/Resources/app/administration
.PHONY: administration-fix

storefront-fix: ## Run eslint on the storefront files
	$(PLATFORM_ROOT)/src/Administration/Resources/app/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config $(PLATFORM_ROOT)/src/Administration/Resources/app/administration/.eslintrc.js --ext .js,.vue --fix src/Resources/app/storefront
.PHONY: storefront-fix

administration-lint: ## Run eslint on the administration files
	$(PLATFORM_ROOT)/src/Administration/Resources/app/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config $(PLATFORM_ROOT)/src/Administration/Resources/app/administration/.eslintrc.js --ext .js,.vue src/Resources/app/administration
.PHONY: administration-lint

storefront-lint: ## Run eslint on the storefront files
	$(PLATFORM_ROOT)/src/Administration/Resources/app/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config $(PLATFORM_ROOT)/src/Administration/Resources/app/administration/.eslintrc.js --ext .js,.vue src/Resources/app/storefront
.PHONY: storefront-lint
