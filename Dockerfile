# Stage 1: PHP dependencies and Composer
FROM php:8.2-fpm-alpine AS php

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    bash \
    curl \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    autoconf \
    g++ \
    make \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath intl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy app source
COPY . .

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev \
    && chown -R www-data:www-data /var/www

# Stage 2: Final image with Nginx + PHP-FPM
FROM php:8.2-fpm-alpine

# Install Nginx and required PHP extensions
RUN apk add --no-cache nginx oniguruma libzip libpng libjpeg-turbo freetype icu-libs

COPY --from=php /usr/local /usr/local
COPY --from=php /var/www /var/www

# Copy nginx config
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Create nginx run directory
RUN mkdir -p /run/nginx

# Set working directory
WORKDIR /var/www

# Expose port 80
EXPOSE 80

# Start nginx and php-fpm
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
