.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

composer-install: ## Run composer install within the host
	docker-compose run --rm \
		php bash -ci '/usr/bin/composer install'

init-db: ## Create and setup the database
	docker-compose run --rm \
		php bash -ci './bin/console doctrine:database:create --if-not-exists && ./bin/console doctrine:schema:update --force'

install:
	$(MAKE) composer-install
	$(MAKE) init-db

start:
	docker-compose up -d

stop:
	docker-compose down

build: ## Build the dockers images
	docker-compose build

bash-php: 
	docker-compose run php bash
