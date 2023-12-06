At Livewire we appreciate and welcome all contributions!

If that's something you would be interested in doing, we recommend going through this contribution guide first before
starting.

## Setup Livewire locally

The first step is to create a fork of Livewire and set it up locally. You should only need to do this the first time.

### Fork Livewire

Go to [the Livewire repository on GitHub](https://github.com/livewire/livewire) and fork the Livewire repository.

### Git clone your fork locally

Browse to your fork on GitHub, and click on the "code" button, and copy the provided URL.

Then in your local terminal run `git clone` and pass it your URL and the directory name you want Livewire cloned into.

```shell
git clone git@github.com:username/livewire.git ~/packages/livewire
```

Once finished, `cd` into your local Livewire directory.

```shell
cd ~/packages/livewire
```

### Install dependencies

Install composer dependencies by running:

```shell
composer install
```

Install npm dependencies by running:

```shell
npm install
```

### Optional: Set up Alpine for local development

If you want to work on Alpine packages at the same time, you can clone the
[Alpine repository](https://github.com/alpinejs/alpine) as well (see above) and use `npm link`:

* `git clone https://github.com/alpinejs/alpine.git ~/packages/alpinejs`
  (make sure to clone somewhere outside your Livewire repo)
* In the cloned Alpine repo, run `npm install` and `npm run build`
* After building successfully, link which ever packages you want to work
  on `cd packages/alpinejs && npm link && cd ../morph && npm link`
* In the Livewire repo, `npm link alpinejs @alpinejs/morph`

> [!tip]
> If you're not updating the JavaScript portions of Livewire in your PR, you don't have to 
> worry about this for running Dusk tests — a built version of the assets is baked into the repo.

### Configure dusk

A lot of Livewire's tests make use of `orchestral/testbench-dusk` which runs browser tests in Google Chrome (so you will
need Chrome to be installed).

To get `orchestral/testbench-dusk` to run, you need to install the latest chrome driver by running:

```shell
./vendor/bin/testbench-dusk dusk:chrome-driver
```

You may also need to run:

```shell
./vendor/bin/dusk-updater update
```

### Run tests

Once everything is configured, run all tests to make sure everything is working and passing.

To do this, run `phpunit` and confirm everything is running ok.

```shell
./vendor/bin/phpunit
```

If the dusk tests don't run and you get an error, make sure you have run the command in
the [Configure dusk](#configure-dusk) section above.

If you still get an error, the first time you try to run dusk tests, you may also need to close any Google Chrome
instances you may have open and try running the tests again. After that, you should be able to leave Chrome open when
running tests.

## Bug fix/feature development

Now it's time to start working on your bug fix or new feature.

### Create a branch

To start working on a new feature or fix a bug, you should always create a new branch in your fork with the name of your
feature or fix.

> [!tip]
> Always create a new branch for your feature or fix.

Do not use your master/ main branch of your fork as maintainers cannot modify PR's submitted from a master/main branch
on a fork.

> [!warning]
> Any PR's submitted from a master/main branch will be closed.

### Add failing tests

The next step is to add failing tests for your code.

Livewire has both Dusk browser tests and standard PHPUnit unit tests, which you can find in `tests/Browser`
and named `UnitTest` throughout the `src/` directory respectively.

Livewire runs both PHP and Javascript code, so Dusk browser tests are preferred to ensure everything works as expected,
and can be supported with unit tests as required.

See below for an example of how a Livewire Dusk test should be structured:

```php
/** @test */
public function it_can_run_foo_action
{
    $this->browse(function ($browser) {
    Livewire::visit($browser, FooComponent::class)
        ->waitForLivewire()->click('@foo')
        ->assertSeeIn('@output', 'foo');
    });
}
```

You can see how to use Dusk in the [Laravel documentation](https://laravel.com/docs/dusk) as well as look at
Livewire's existing browser tests for further examples.

### Add working code

Livewire has both PHP and javascript code, which you can find in the `src` directory for PHP and the `js` directory for
javascript.

Change the code as required to fix the bug or add the new feature, but try to keep changes to a minimum. Consider
splitting into multiple PR's if required.

> [!warning]
> PR's that make too many changes or make unrelated changes may be closed.

If you have updated any of Livewire's javascript code, you will need to recompile the assets.
To do this run `npm run build`, or you may start a watcher with `npm run watch`.

Compiled javascript assets should be committed with your changes.

> [!tip]
> If you update any javascript, make sure to recompile assets and commit them.

Once you have finished writing your code, do a review to ensure you haven't left any debugging code and formatting
matches the existing style.

### Run tests

The final step before submitting is to run all tests to ensure your changes haven't impacted anything else.

To do this, run `phpunit` and confirm everything is running ok.

```shell
./vendor/bin/phpunit
```

If the Dusk browser tests don't run, see [Run tests](#setup-run-tests) in the Setup section above for more details

### Submit PR

Once all tests pass, then push your branch up to GitHub and submit your PR.

In your PR description make sure to provide a small example of what your PR does along with a thorough description of
the improvement and reasons why it's useful.

Add links to any issues or discussions that are relevant for further details.

> [!tip]
> For first-time contributors, tests won't run automatically, so they will need to be started by a maintainer.

### Thanks for contributing! 🙌

And that's it!

Maintainers will review your PR and give feedback as required.

Thanks for contributing to Livewire!
