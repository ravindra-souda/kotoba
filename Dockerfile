FROM dunglas/frankenphp:sha-69416cc-php8.2-alpine@sha256:a5b9d58f6ee3298b05cf3113c76267b6d5a34afe8259e0a37089001beb614d9c AS base

RUN install-php-extensions mongodb-1.16.2
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
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache git unzip
USER ${APP_USER}

# vendor: Used for prod, get dependencies without any dev tools or scripts
FROM base AS vendor
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
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
