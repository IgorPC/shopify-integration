#!/bin/bash

composer install

php artisan key:generate --force

echo "Waiting for database..."
until php artisan db:monitor; do
  sleep 2
done

php artisan migrate --force

php artisan optimize:clear

php artisan queue:work --verbose --tries=3 &

echo "----> API successfully built !"

php artisan serve --host=0.0.0.0 --port=80
