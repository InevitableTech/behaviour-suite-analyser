install:
	docker-compose run php-test sh -c "composer install"
	docker-compose run php-test sh -c "cd testproject && composer install"
	docker-compose run php-test sh -c "cd vendor/genesis/bdd-analyser-rules && composer install"

update:
	docker-compose run php-test sh -c "composer update"

global-install:
	docker-compose run php-test sh -c "php -v && composer global require genesis/bdd-analyser"

.PHONY: tests
tests:
	docker-compose run php-test sh -c "php -v && ./vendor/bin/phpunit tests"
	docker-compose run php-test sh -c "bin/bdd-analyser scan ."

tests-deps:
	docker-compose run php-test sh -c "php -v && cd ./vendor/genesis/bdd-analyser-rules && ./vendor/bin/phpunit tests"
