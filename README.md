# Laravel Livewire

## Get set up for local development and contribution

Note: I'm assuming you have a folder for all your projects and are serving that with Valet.

1. Pull down `livewire`: `git clone https://github.com/calebporzio/livewire.git`
2. Create or `cd` into a Laravel project that shares the same parent folder as `livewire`
3. Run `composer config repositories.local '{"type": "path", "url": "../livewire"}' --file composer.json`
4. Now `composer require calebporzio/livewire:dev-master`
5. View documentation here: https://goofy-tereshkova-ff1e2b.netlify.com/docs/quickstart/
6. Contribute to documentation here: https://github.com/calebporzio/livewire-docs

## Thank You's

- Thanks to @davidpiesse for helping a ton with the event emission idea and implementation.
