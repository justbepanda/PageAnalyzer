PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

install:
	composer install

console:
	composer exec --verbose psysh

lint:
	composer exec --verbose phpcs -- --standard=phpcs.xml app tests
	composer exec --verbose phpstan -- analyse -c phpstan.neon

lint-fix:
	composer exec --verbose phpcbf -- --standard=phpcs.xml app tests

test:
	composer exec --verbose phpunit tests

test-coverage:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

test-coverage-text:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-text

test-coverage-html:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-html coverage

validate:
	composer validate