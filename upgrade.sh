#!/bin/sh

cp composer.json _composer_backup.json

composer config repositories.livewire-next --file composer.json '{ "type": "package", "package": { "name": "livewire/livewire", "version": "3.0.0", "dist": { "url": "https://calebporzio-public.s3.amazonaws.com/next-main.zip", "type": "zip" }, "autoload": { "files": [ "src/helpers.php" ], "psr-4": { "Livewire\\": "src/" } }, "extra": { "laravel": { "providers": [ "Livewire\\ServiceProvider" ], "aliases": { "Livewire": "Livewire\\Livewire" } } } } }'

composer require livewire/livewire:3.0.0

php artisan view:clear

