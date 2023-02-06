install:
	composer install
	cd testproject && composer install && cd -
	cd vendor/forceedge01/bdd-analyser-rules && composer install && cd -

.PHONY: tests
tests:
	./vendor/bin/phpunit tests
	cd ./vendor/forceedge01/bdd-analyser-rules && ./vendor/bin/phpunit tests
