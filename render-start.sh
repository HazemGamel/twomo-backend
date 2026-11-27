#!/bin/bash
set -e

# Copy default .env if not present
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure APP_KEY exists
php artisan key:generate --force || true

# Clear cache and run database migrations
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan migrate --force

# Seed database
php artisan db:seed --force || true

# Ensure correct permissions on storage and database
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Update Apache port to listen to Render's PORT environment variable
PORT="${PORT:-80}"
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Start Apache in foreground
exec apache2-foreground
