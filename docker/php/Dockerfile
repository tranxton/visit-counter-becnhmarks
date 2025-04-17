FROM php:8.4-alpine AS php

COPY --from=composer:2                  /usr/bin/composer /usr/bin/composer
COPY --from=spiralscout/roadrunner:2024 /usr/bin/rr       /usr/bin/rr

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apk update && apk upgrade && apk add --no-cache \
    oniguruma-dev \
    linux-headers \
    libxml2-dev \
    libpq-dev \
    autoconf \
    icu-dev \
	g++ \
	make \
    git \
    zip \
    && docker-php-ext-configure \
    intl \
    && docker-php-ext-install \
    mbstring \
    session \
    simplexml \
    opcache \
    sockets \
    intl \
    && pecl install  \
    redis \
    && docker-php-ext-enable  \
    redis \
    opcache

COPY ./docker/php/configs/opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

WORKDIR /app
RUN git config --global --add safe.directory /app

FROM php AS dev

RUN pecl install \
    xdebug \
    && docker-php-ext-enable \
    xdebug

COPY ./docker/php/configs/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

CMD ["rr", "serve", "-c", ".rr.dev.yaml"]

FROM php AS prod

CMD ["rr", "serve", "-c", ".rr.yaml"]
