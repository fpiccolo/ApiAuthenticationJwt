# Executables (local)
DOCKER_COMP = docker-compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php


init: start composer-install migrate fixture

destroy:
	@$(DOCKER_COMP) down --remove-orphans -v

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer-install:
	@$(PHP_CONT) composer install


## —— Doctrine ————————————————————————————————————————————————————————————————
diff:
	@$(PHP_CONT) bin/console --no-interaction doctrine:migration:diff

migrate:
	@$(PHP_CONT) bin/console --no-interaction doctrine:migration:migrate

fixture:
	@$(PHP_CONT) bin/console --no-interaction doctrine:fixtures:load

## —— Tests ————————————————————————————————————————————————————————————————
test:
	@$(PHP_CONT) php -dxdebug.mode=coverage bin/phpunit --testdox --coverage-text