# ============================================================
# Stage 1: Frontend
# ============================================================
FROM node:latest AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build


# ============================================================
# Stage 2: Composer
# ============================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

ARG "GITHUB_TOKEN"

RUN if [ -n "$GITHUB_TOKEN" ]; then \
        composer config -g github-oauth.github.com "$GITHUB_TOKEN"; \
    fi

RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
        --no-dev \
        --prefer-dist \
        --optimize-autoloader \
        --no-interaction \
        --no-progress \
        --no-scripts

COPY . .

RUN composer dump-autoload --optimize


# ============================================================
# Stage 3: Production
# ============================================================
FROM php:8.4-fpm AS production

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        nginx \
        unzip \
        libfcgi-bin \
        zip \
        curl \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove \
        autoconf \
        dpkg-dev \
        file \
        g++ \
        gcc \
        libc-dev \
        make \
        pkg-config \
        re2c \
    && rm -rf /var/lib/apt/lists/*


# ============================================================
# PHP Configuration
# ============================================================
COPY docker/php/opcache.ini \
    /usr/local/etc/php/conf.d/opcache.ini


# ============================================================
# Application
# ============================================================
WORKDIR /var/www/html

COPY --chown=www-data:www-data . .

COPY --chown=www-data:www-data \
    --from=vendor /app/vendor ./vendor

COPY --chown=www-data:www-data \
    --from=frontend /app/public/build ./public/build


# ============================================================
# Laravel permissions
# ============================================================
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache


# Entrypoint
COPY docker/php/entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

USER www-data

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]


CMD ["/usr/local/bin/docker-entrypoint.sh"]
