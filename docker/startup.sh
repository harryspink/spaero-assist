#!/bin/sh

echo "Starting SSH on port 2222..."
/usr/sbin/sshd

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
nginx -g 'daemon off;'
