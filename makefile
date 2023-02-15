install:
	docker-compose run php-test sh -c "composer install"
	docker-compose run php-test sh -c "cd testproject && composer install"
	docker-compose run php-test sh -c "cd vendor/forceedge01/bdd-analyser-rules && composer install"

update:
	docker-compose run php-test sh -c "composer update"

command:
	docker-compose run php-test sh -c "$(command)"

install-dev:
	docker-compose run php-test sh -c "composer install --prefer-source"
	docker-compose run php-test sh -c "cd testproject && composer install --prefer-source"
	docker-compose run php-test sh -c "cd vendor/forceedge01/bdd-analyser-rules && composer install --prefer-source"

global-install:
	docker-compose run php-test sh -c "php -v && composer global require forceedge01/bdd-analyser"

.PHONY: tests
tests:
	docker-compose run php-test sh -c "php -v && ./vendor/bin/phpunit tests"
	docker-compose run php-test sh -c "bin/bdd-analyser scan ."

tests-deps:
	docker-compose run php-test sh -c "php -v && cd ./vendor/forceedge01/bdd-analyser-rules && ./vendor/bin/phpunit tests"

run:
	docker-compose run php-test sh -c "cd testproject && vendor/bin/bdd-analyser scan ."

run-local:
	bdd-analyser scan .
