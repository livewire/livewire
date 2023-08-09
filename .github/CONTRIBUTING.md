
### Getting set up locally

(This is a work-in-progress)

* `git clone`
* `composer install`
* `./vendor/bin/testbench-dusk dusk:chrome-driver`
* (`./vendor/bin/dusk-updater update` also sometimes fixes chromedriver issues)

Please consult the old v2 docs for [general guidelines for contributing](https://laravel-livewire.com/docs/2.x/contribution-guide).

If you want to run a single test instead of the whole test suite, you can do:

`./vendor/bin/phpunit --filter path/to/your/test.php`

> [!NOTE]
> At this point in the beta, building the JavaScript assets locally can be complicated. But if you're not updating the JavaScript portions of Livewire in your PR, you don't have to worry about this for running Dusk tests — a built version of the assets is baked into the repo.
