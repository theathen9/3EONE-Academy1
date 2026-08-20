# ============================================================
# Stage 1: Frontend Build
# ============================================================
ARG GITHUB_TOKEN

FROM node:24.16.0-alpine AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build


# ============================================================
# Stage 2: Composer Dependencies
# ============================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

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
# Stage 3: Production PHP-FPM
# ============================================================
FROM php:8.4-fpm AS production

# ============================================================
# System Dependencies + PHP Extensions
# ============================================================
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        curl \
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
# Entrypoint
# ============================================================
COPY docker/php/entrypoint.sh \
    /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh


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
# Laravel Storage / Cache Permissions
# ============================================================
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache


# ============================================================
# Runtime User
# ============================================================
USER www-data

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["php-fpm"]