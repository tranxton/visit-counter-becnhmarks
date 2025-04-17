DOCKER_RUN_K6=docker-compose run --rm k6 run
DOCKER_EXEC_PHP=docker-compose exec php

.php.env.init:
	@echo "Initializing .env.local..."
	@$(DOCKER_EXEC_PHP) sh -c "if ! ls .env.local >/dev/null 2>&1; then cp .env .env.local; fi"
	@echo ".env.local initialized."

.php.env.set-prod:
	@echo "Setting application environment to prod..."
	@$(DOCKER_EXEC_PHP) sed -i -e 's/APP_ENV=dev/APP_ENV=prod/' -e 's/APP_DEBUG=1/APP_DEBUG=0/' .env.local
	@echo "Application environment set to prod."

.php.env.set-dev:
	@echo "Setting application environment to dev..."
	@$(DOCKER_EXEC_PHP) sed -i -e 's/APP_ENV=prod/APP_ENV=dev/' -e 's/APP_DEBUG=0/APP_DEBUG=1/' .env.local
	@echo "Application environment set to dev."

.php.env.secret.generate:
	@echo "Generating APP_SECRET..."
	@$(DOCKER_EXEC_PHP) sh -c 'if grep -q "^APP_SECRET=" .env.local; then sed -i "s/^APP_SECRET=.*/APP_SECRET=$$(openssl rand -hex 32)/" .env.local; fi'
	@echo "APP_SECRET generated."

.php.composer.first-install:
	@echo "Installing composer dependencies..."
	@docker run --rm -it -v ./php:/app -w /app composer:2 composer first-install
	@echo "Composer dependencies installed."

.php.composer.check:
	@echo "Installing validating & auditing composer.json..."
	@$(DOCKER_EXEC_PHP) composer validate && $(DOCKER_EXEC_PHP) composer audit
	@echo "composer.json is valid."

php.build.prod: \
	.php.env.set-prod \
	php.composer.install-prod \
	php.composer.dump-prod-env \
	php.cache.clear \
	docker.php.restart

php.build.dev: \
	.php.env.set-dev \
	php.composer.install-dev \
	php.composer.remove-prod-env \
	php.cache.clear \
	docker.php.restart

php.cache.clear:
	@echo "Clearing the cache..."
	@$(DOCKER_EXEC_PHP) bin/console cache:clear
	@echo "Cache was successfully cleared."

php.composer.dump-prod-env:
	@echo "Dumping prod .env..."
	@$(DOCKER_EXEC_PHP) composer dump-env prod
	@echo "Successfully dumped prod .env file."

php.composer.remove-prod-env:
	@echo "Removing prod .env.local.php dump..."
	@$(DOCKER_EXEC_PHP) rm -f .env.local.php
	@echo "Removed .env.local.php dump."

php.composer.install-prod:
	@echo "Installing dev dependencies..."
	@$(DOCKER_EXEC_PHP) composer install-prod
	@echo "Prod dependencies installed."

php.composer.install-dev:
	@echo "Installing dev dependencies..."
	@$(DOCKER_EXEC_PHP) composer install-dev
	@echo "Dev dependencies installed."

php.tests.run:
	@echo "Running tests..."
	$(DOCKER_EXEC_PHP) bin/phpunit
	@echo "Tests ran successfully."

php.init: \
	.php.composer.first-install \
    docker.up \
    .php.env.init \
    .php.env.secret.generate \
    php.cache.clear \
    .php.composer.check \
    php.tests.run

init: \
	php.init

k6.test.run:
	@echo "Running tests..."
	@$(DOCKER_RUN_K6) $(test)
	@echo "Running tests..."

docker.up:
	@echo "Starting docker containers..."
	@docker-compose up -d
	@echo "Docker containers started."

docker.stop:
	@@echo "Stopping docker containers..."
	@@docker-compose stop
	@@echo "Docker containers stopped."

docker.down:
	@echo "Removing docker containers..."
	@docker-compose down
	@echo "Docker containers removed."

docker.php.restart:
	@echo "Restarting PHP container..."
	@docker-compose restart php
	@echo "PHP container restarted."