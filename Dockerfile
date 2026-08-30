# ============================================================
# Stage 1: Frontend
# ============================================================
FROM node:22-alpine AS frontend

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

ENV APP_ENV=production
ENV APP_DEBUG=false

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
# Nginx Configuration
# ============================================================
RUN rm -f /etc/nginx/conf.d/default.conf

COPY docker/nginx/nginx.conf \
    /etc/nginx/nginx.conf

COPY docker/nginx/default.conf \
    /etc/nginx/conf.d/default.conf


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
# Entrypoint
# ============================================================
COPY docker/nginx/entrypoint.sh \
    /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh


# IMPORTANT:
# Do NOT use USER www-data here.
# Nginx/PHP-FPM startup needs root privileges.
# PHP-FPM workers will run as www-data.

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
