install:
	docker-compose run php7.1-test sh -c "composer install"
	docker-compose run php7.1-test sh -c "cd testproject && composer install"
	docker-compose run php7.1-test sh -c "cd vendor/forceedge01/bdd-analyser-rules && composer install"

install-dev:
	docker-compose run php7.1-test sh -c "composer install --prefer-source"
	docker-compose run php7.1-test sh -c "cd testproject && composer install --prefer-source"
	docker-compose run php7.1-test sh -c "cd vendor/forceedge01/bdd-analyser-rules && composer install --prefer-source"

clean:
	docker-compose run php7.1-test sh -c "rm -rf composer.lock"
	docker-compose run php7.1-test sh -c "rm -rf vendor"
	docker-compose run php7.1-test sh -c "rm -rf testproject/vendor"

global-install:
	docker-compose run php7.1-test sh -c "php -v && composer global require forceedge01/bdd-analyser"

command:
	docker-compose run php7.1-test sh -c "$(command)"

update:
	docker-compose run php7.1-test sh -c "composer update"

require:
	docker-compose run php7.1-test sh -c "composer require $(r)"

.PHONY: tests
tests:
	docker-compose run php7.1-test sh -c "php -v && ./vendor/bin/phpunit tests"
	docker-compose run php7.1-test sh -c "bin/bdd-analyser scan ."

tests-deps:
	docker-compose run php7.1-test sh -c "php -v && cd ./vendor/forceedge01/bdd-analyser-rules && ./vendor/bin/phpunit tests"

run:
	docker-compose run php7.1-test sh -c "cd testproject && vendor/bin/bdd-analyser scan ."

run-local:
	bdd-analyser scan .
