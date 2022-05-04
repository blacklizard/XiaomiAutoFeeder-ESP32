#!/bin/bash
composer install --optimize-autoloader --no-dev
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache
php artisan event:clear
php artisan event:cache
php artisan queue:restart

