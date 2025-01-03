install:
	composer install

lint:
	composer exec phpcs -- --standard=PSR12 src
