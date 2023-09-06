
### Getting set up locally

(This is a work-in-progress)

* `git clone`
* `composer install`
* `./vendor/bin/testbench-dusk dusk:chrome-driver`
* (`./vendor/bin/dusk-updater update` also sometimes fixes chromedriver issues)

Please consult the old v2 docs for [general guidelines for contributing](https://laravel-livewire.com/docs/2.x/contribution-guide).


If you want to run a single test instead of the whole test suite, you can do:

`./vendor/bin/phpunit path/to/your/test.php`

#### Compiling the JavaScript assets locally

* `npm install`
* `npm run build`

If you want to work on Alpine packages at the same time, you can clone the Alpine repo and use `npm link`:

* `git clone https://github.com/alpinejs/alpine.git` outside of the Livewire repo
* In the cloned Alpine repo, run `npm install` & `npm run build`
* After building successfully, link which ever packages you want to work on `cd packages/alpinejs && npm link && cd ../morph && npm link`
* In the Livewire repo, `npm link alpinejs @alpinejs/morph`

> [!NOTE]
> If you're not updating the JavaScript portions of Livewire in your PR, you don't have to worry about this for running Dusk tests — a built version of the assets is baked into the repo.
