FROM phpdockerio/php:8.1-fpm
WORKDIR "/app"

RUN apt-get update \
    && apt-get install -y make \
    && apt-get -y --no-install-recommends install \
        git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*