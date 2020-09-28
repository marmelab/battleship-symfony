.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

composer-install: ## Run composer install within the host
	docker-compose run --rm \
		php bash -ci '/usr/bin/composer install'

init-db: ## Create and setup the database
	docker-compose run --rm \
		php bash -ci './bin/console doctrine:database:create --if-not-exists'

migrate: ## Migrate the database
	docker-compose run --rm \
		php bash -ci './bin/console doctrine:migrations:diff && ./bin/console doctrine:migrations:migrate --no-interaction'

install: ## Install dependencies and setup database
	$(MAKE) composer-install
	$(MAKE) init-db
	$(MAKE) migrate

start: ## Start server
	docker-compose up

stop: ## Stop server
	docker-compose down

build: ## Build docker images
	docker-compose build

bash-php: ## Open a bash in php container
	docker-compose run php bash

test: ## Launch phpunit tests
	docker-compose run --rm \
		php bash -ci './bin/console doctrine:database:create --env=test && ./bin/console doctrine:fixtures:load --env=test --no-interaction && bin/phpunit'

test-go: ## Launch go tests
	docker-compose run --rm --no-deps api go test -v ./test/...

lint-go: ## Use golint to check the code
	docker-compose run --rm --no-deps api golint ./src/battleship/...
