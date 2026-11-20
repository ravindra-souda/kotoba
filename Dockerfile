FROM dunglas/frankenphp:1.13.6-php8.5-alpine@sha256:0b064e00fd2cc0c72546da62f02daeee709635879730d34cbde1922ea43e6b94 AS base

RUN install-php-extensions mongodb-2.5.8 intl
WORKDIR /app

# running as a non-root user
ENV APP_USER=kotoba
RUN adduser -D ${APP_USER}

# remove capability to bind the web server on privileged ports (80 and 443)
RUN setcap -r /usr/local/bin/frankenphp

RUN chown -R ${APP_USER}:${APP_USER} /config/caddy /data/caddy

ENV SERVER_NAME=:8080
EXPOSE 8080

# dev: Get Composer, Git and ends here
FROM base AS dev
COPY --from=composer:2.11 /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache git unzip

ENV COMPOSER_HOME=/home/${APP_USER}/.composer
USER ${APP_USER}

# vendor: Used for prod, get dependencies without any dev tools or scripts
FROM base AS vendor
COPY --from=composer:2.11 /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache git unzip

RUN chown ${APP_USER}:${APP_USER} /app
COPY composer.json composer.lock ./

USER ${APP_USER}
RUN composer install --no-dev --no-scripts --no-autoloader

COPY --chown=${APP_USER}:${APP_USER} . .
RUN composer dump-autoload --no-dev --optimize

# prod: Without Composer or Git, only dependencies are copied
FROM base AS prod
COPY --from=vendor /app /app
USER ${APP_USER}
