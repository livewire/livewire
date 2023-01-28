#!/bin/sh

rm composer.json && cp  _composer_backup.json composer.json

rm composer.lock

composer install

php artisan view:clear
