install:
	docker-compose run php7.1-test sh -c "composer install"
	docker-compose run php7.1-test sh -c "cd testproject && composer install"
	docker-compose run php7.1-test sh -c "cd vendor/forceedge01/bdd-analyser-rules && composer install"

global-install:
	docker-compose run php7.1-test sh -c "php -v && composer global require forceedge01/bdd-analyser"

update:
	docker-compose run php7.1-test sh -c "composer update"

.PHONY: tests
tests:
	docker-compose run php7.1-test sh -c "php -v && ./vendor/bin/phpunit tests"
	docker-compose run php7.1-test sh -c "bin/bdd-analyser scan ."

tests-deps:
	docker-compose run php7.1-test sh -c "php -v && cd ./vendor/forceedge01/bdd-analyser-rules && ./vendor/bin/phpunit tests"
