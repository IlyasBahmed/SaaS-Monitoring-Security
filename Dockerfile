# =========================
# Stage 1 — Composer
# =========================
FROM php:8.3-cli-alpine AS composer


# Installer PHP extensions helper
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions

# PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    mongodb \
    zip \
    bcmath \
    pcntl \
    exif \
    intl \
    opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy all project
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# =========================
# Stage 2 — Frontend Build
# =========================
FROM node:22-alpine AS node

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install node modules
RUN npm ci

# Copy frontend files
COPY . .

# Build Vite assets
RUN npm run build

# =========================
# Stage 3 — Production
# =========================
FROM php:8.3-fpm-alpine
RUN apk update && apk upgrade --no-cache

# Installer PHP extensions helper
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions

# Install PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    mongodb \
    zip \
    bcmath \
    pcntl \
    exif \
    intl \
    opcache

WORKDIR /var/www

# Copy project
COPY . .

# Copy vendor from composer stage
COPY --from=composer /app/vendor ./vendor

# Copy built frontend assets
COPY --from=node /app/public/build ./public/build

# Remove old Laravel caches
RUN rm -rf bootstrap/cache/*.php

# Laravel folders permissions
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Optimize Laravel
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

EXPOSE 9000

CMD ["php-fpm"]
