FROM dunglas/frankenphp:sha-69416cc-php8.2-alpine@sha256:a5b9d58f6ee3298b05cf3113c76267b6d5a34afe8259e0a37089001beb614d9c AS base

RUN install-php-extensions mongodb-1.16.2
WORKDIR /app

# dev: Get Composer and ends here
FROM base AS dev
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# vendor: Used for prod, get dependencies without any dev tools or scripts
FROM base AS vendor
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# user for Composer
ARG USER=appuser
RUN adduser -D ${USER}
RUN chown ${USER}:${USER} /app
COPY composer.json composer.lock ./

USER ${USER}
RUN composer install --no-dev --no-scripts --no-autoloader

COPY --chown=${USER}:${USER} . .
RUN composer dump-autoload --no-dev --optimize

# prod: Without Composer, only dependencies are copied
FROM base AS prod
COPY --from=vendor /app /app
