install:
	composer install

lint:
	composer exec phpcs -- --standard=PSR12 src

lint-fix:
	composer exec --verbose phpcbf -- src tests