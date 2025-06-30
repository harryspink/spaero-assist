# Stage 1: Build and Composer
FROM php:8.2-fpm-alpine AS php

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
    openssh \
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

# Stage 2: Runtime
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
    bash \
    curl \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    icu-libs \
    oniguruma \
    openssh \
    supervisor \
    && mkdir -p /home/site/wwwroot /home/LogFiles /opt/startup /run/nginx /run/sshd \
    && echo "root:Docker!" | chpasswd \
    && sed -i 's/#Port 22/Port 2222/' /etc/ssh/sshd_config \
    && sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config

# Copy PHP and app
COPY --from=php /usr/local /usr/local
COPY --from=php /var/www /var/www

# Copy Nginx config
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy startup script
COPY docker/startup.sh /opt/startup/startup.sh
RUN chmod +x /opt/startup/startup.sh

# Set working directory
WORKDIR /var/www

# Ports: 80 (web), 2222 (Azure SSH)
EXPOSE 80 2222

# Start SSH, Laravel, PHP, and Nginx
CMD ["/opt/startup/startup.sh"]
