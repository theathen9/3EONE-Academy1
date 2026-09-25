# ============================================================
# 1. Frontend
# ============================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build


# ============================================================
# 2. Composer dependencies
# ============================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

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
# 3. PHP / Laravel Application
# ============================================================
FROM php:8.4-fpm-bookworm AS app

ENV APP_ENV=production
ENV APP_DEBUG=false

WORKDIR /var/www/html


# ============================================================
# System dependencies + PHP extensions
# ============================================================
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        curl \
        unzip \
        zip \
        libfcgi-bin \
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
    \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    \
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
    \
    && pecl install redis \
    && docker-php-ext-enable redis \
    \
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
    \
    && rm -rf /var/lib/apt/lists/*


# ============================================================
# Nginx Configuration
# ============================================================
RUN rm -f /etc/nginx/sites-enabled/default \
          /etc/nginx/conf.d/default.conf

COPY docker/nginx/nginx.conf \
    /etc/nginx/nginx.conf

COPY docker/nginx/default.conf \
    /etc/nginx/conf.d/default.conf


# ============================================================
# Laravel Application
# ============================================================
COPY . .

COPY --from=vendor /app/vendor ./vendor

COPY --chown=www-data:www-data \
    --from=frontend /app/public/build \
    ./public/build


# ============================================================
# Laravel Permissions
# ============================================================
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R ug+rwx \
        storage \
        bootstrap/cache


# ============================================================
# Laravel package discovery
# ============================================================
RUN php artisan package:discover --ansi


# ============================================================
# Entrypoint
# ============================================================
COPY docker/nginx/entrypoint.sh \
    /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh


# ============================================================
# Runtime
# ============================================================
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
