#!/bin/bash
set -e

# Run migrations and database setup
php artisan config:clear
php artisan migrate --force

# Seed database with initial borders & demo data if tables are empty
php artisan db:seed --force || true

# Update Apache port to listen to Render's PORT environment variable
PORT="${PORT:-80}"
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Start Apache in foreground
exec apache2-foreground
