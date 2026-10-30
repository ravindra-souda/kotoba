FROM dunglas/frankenphp:1.13.4-php8.5-alpine@sha256:974e767f7694964fdb23ead3172dd0a12cedefbd929fbe0db7f69a680663295d AS base

RUN install-php-extensions mongodb-2.5.5
WORKDIR /app

# dev: Get Composer and ends here
FROM base AS dev
COPY --from=composer:2.11 /usr/bin/composer /usr/bin/composer

# vendor: Used for prod, get dependencies without any dev tools or scripts
FROM base AS vendor
COPY --from=composer:2.11 /usr/bin/composer /usr/bin/composer

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
