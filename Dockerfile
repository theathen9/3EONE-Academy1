<<<<<<< Updated upstream
# ============================================================
# Stage 1: Frontend
# ============================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

=======
# =========================================================
# 1. Node / Vite build
# =========================================================

FROM node:24.16.0-alpine AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

>>>>>>> Stashed changes
RUN npm run build


# =========================================================
# 2. Composer dependencies
# =========================================================

FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

<<<<<<< Updated upstream
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
        --no-dev \
        --prefer-dist \
        --optimize-autoloader \
        --no-interaction \
        --no-progress \
        --no-scripts
=======
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --no-scripts
>>>>>>> Stashed changes

COPY . .

RUN composer dump-autoload --optimize


# =========================================================
# 3. Application
# =========================================================

<<<<<<< Updated upstream
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
=======
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    unzip \
    zip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
>>>>>>> Stashed changes
    && rm -rf /var/lib/apt/lists/*


# =========================================================
# PHP extensions
# =========================================================

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

RUN docker-php-ext-install -j"$(nproc)" \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pdo_pgsql \
    pgsql \
    zip


<<<<<<< Updated upstream
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
=======
# =========================================================
# PHP configuration
# =========================================================

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' \
    /usr/local/etc/php-fpm.d/www.conf


# =========================================================
# Laravel
# =========================================================

>>>>>>> Stashed changes
WORKDIR /var/www/html

COPY . .

COPY --from=vendor /app/vendor ./vendor

<<<<<<< Updated upstream
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
=======
COPY --from=frontend /app/public/build ./public/build


# =========================================================
# Laravel permissions
# =========================================================

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache


# =========================================================
# Laravel package discovery
# =========================================================
>>>>>>> Stashed changes

RUN php artisan package:discover --ansi

<<<<<<< Updated upstream

# IMPORTANT:
# Do NOT use USER www-data here.
# Nginx/PHP-FPM startup needs root privileges.
# PHP-FPM workers will run as www-data.

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
=======

# =========================================================
# Nginx
# =========================================================

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf


# =========================================================
# Startup
# =========================================================

COPY docker/nginx/start.sh /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
>>>>>>> Stashed changes
