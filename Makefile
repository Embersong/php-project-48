install:
	composer install

lint:
	composer exec phpcs -- --standard=PSR12 src

lint-fix:
	composer exec --verbose phpcbf -- src tests

test:
	composer exec --verbose phpunit tests

test-coverage:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-clover=build/logs/clover.xml

test-coverage-text:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-text

run1:
	php ./bin/gendiff ./tests/fixtures/file1.yaml ./tests/fixtures/file2.yaml
run2:
	php ./bin/gendiff ./tests/fixtures/file1.json ./tests/fixtures/file2.json
