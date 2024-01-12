Hi there and welcome to the Livewire contribution guide. In this guide we are going to take a look at how you can contribute back to Livewire by submitting new features, failing tests, or bug fixes.

## Setting up Livewire and Alpine locally
In order to contribute back the easiest way to do this is by ensuring the Livewire and Alpine repositories are setup on your machine locally to easily make changes and run the test suite.

To make this process as easy as possible Livewire provides an CLI command that will setup everything you need. Alternatively you can look at doing the steps manually in the next section.

#### Prerequisites
1. You need to have the [Github CLI](https://cli.github.com/) installed on your machine and authenticated with your Github account.
2. You need to have [NPM](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) installed on your machine.
#### Installing the Livewire CLI
First, let's install the CLI as a global dependency using Composer. This will ensure we can run the `livewire-cli` command everywhere.

````shell
composer global require livewire/cli
````

#### Forking and cloning the repositories
The next step is to fork and clone the required repositories using the Livewire CLI. For the best experience it's recommended to run this command from the directory that contains your projects like `~/Sites`, `~/Developer`, `~/Code`, etc.

Let's run the `setup-source` command to initiate the process:

```shell
 Welcome to the Livewire source setup wizard!

 This command will fork and download the Livewire and Alpine repository to your current working directory.

 The best location would probably be your projects folder like ~/Developer, ~/Code, ~/Sites, etc.

 This command is only required if you want to contribute to Livewire core.
```

We start by choosing the directories we want to use for the Livewire and Alpine repository. The easiest is to use the defaults and hit `enter` to start the installation process:

```shell
 ┌ Directory to clone Livewire into ────────────────────────────┐
 │ livewire                                                     │
 └──────────────────────────────────────────────────────────────┘
 
 ┌ Directory to clone Alpine into ──────────────────────────────┐
 │ alpine                                                       │
 └──────────────────────────────────────────────────────────────┘
 
 [√] Livewire repository forked.

 [√] Livewire repository cloned.

 [√] Composer dependencies installed.
 
 [√] Laravel Dusk configured.
 
 [√] Alpine repository forked.
 
 [√] Alpine repository cloned.
 
 [√] Alpine npm dependencies installed.

 [√] Alpine build created.

 [√] Global Alpine link created.

 [√] Livewire and Alpine are linked.

 [√] Livewire build created.

 ----------------------------------

 [√] The Livewire source code is now ready to be worked on.

 From the Livewire directory you can run the following commands:

+------------------------------+------------------------------------------+
| Description                  | Command                                  |
+------------------------------+------------------------------------------+
| Watch and compile JS changes | npm run watch                            |
| Run tests                    | ./vendor/bin/phpunit                     |
| Run Unit tests               | ./vendor/bin/phpunit --testsuite Unit    |
| Run Browser tests            | ./vendor/bin/phpunit --testsuite Browser |
| Run Legacy tests             | ./vendor/bin/phpunit --testsuite Legacy  |
+------------------------------+------------------------------------------+

 From the Alpine directory you can run the following commands:

+------------------------------+---------------+
| Description                  | Command       |
+------------------------------+---------------+
| Watch and compile JS changes | npm run watch |
| Run tests                    | npm run test  |
+------------------------------+---------------+
```

Perfect! We can now watch and compile Javascript changes, run the test suites for both Livewire and Alpine. Give it a try and run the Unit test suite for Livewire:

```shell
./vendor/bin/phpunit --testsuite Unit
```

#### Forking and cloning the repositories manually
If you don't want to use the CLI command you can run the following commands to setup Livewire manually:

```shell
# Fork and clone Livewire
gh repo fork livewire/livewire --default-branch-only --clone=true --remote=false -- livewire

# Switch the working directory to livewire
cd livewire

# Install all composer dependencies
composer install

# Ensure Dusk is correctly configured
vendor/bin/dusk-updater detect --no-interaction
```

To setup Alpine you can run the following commands:

```shell
# Fork and clone Alpine
gh repo fork alpinejs/alpine --default-branch-only --clone=true --remote=false -- alpine

# Switch the working directory to alpine
cd alpine

# Install all npm dependencies
npm install

# Build all Alpine packages
npm run build

# Link all Alpine package locally 
cd alpine/packages/alpinejs && npm link"
cd alpine/packages/anchor && npm link"
cd alpine/packages/collapse && npm link"
cd alpine/packages/csp && npm link"
cd alpine/packages/docs && npm link"
cd alpine/packages/focus && npm link"
cd alpine/packages/history && npm link"
cd alpine/packages/intersect && npm link"
cd alpine/packages/mask && npm link"
cd alpine/packages/morph && npm link"
cd alpine/packages/navigate && npm link"
cd alpine/packages/persist && npm link"

# Switch the working directory back to livewire
cd ../livewire

# Link all packages
npm link alpinejs @alpinejs/anchor @alpinejs/collapse @alpinejs/csp @alpinejs/docs @alpinejs/focus @alpinejs/history @alpinejs/intersect @alpinejs/mask @alpinejs/morph @alpinejs/navigate @alpinejs

# Build Livewire
npm run build
```

## Contributing a failing test
It could be that you are experiencing a bug but you have no idea how to solve it. The Livewire core can be quite complex and overwhelming so where do you begin? In this case, the easiest would be to contribute a failing test and have someone with more experience help fix the bug. We do however recommend you to take a look at the core and get a better understanding of how Livewire works.

Let's take a look at an step by step example. 
##### 1. Determine where to add your test
The Livewire core is separated in different folders based on specific Livewire features. For example: 

```shell
SupportAccessingParent
SupportAttributes
SupportAutoInjectedAssets
SupportBladeAttributes
SupportChecksumErrorDebugging
SupportComputed
SupportConsoleCommands
SupportDataBinding
//...
```

Try and see if you can locate an feature that is related to the bug you are experiencing. If you can't find any or if you aren't sure which one to pick just choose one and mention in your pull request that you need some assistance with placing the test in the correct feature set.

##### 2. Determine the type of test
The Livewire test suite consists out of two type of tests:
1. **Unit tests**: These test focus on the PHP implementation of Livewire.
2. **Browser tests:** These test run a series of steps inside a real browser and asserting the correct outcome. These tests mostly focus on the Javascript implementation of Livewire.
If you don't really know which type of test you should pick or when you are unfamiliar with writing tests for Livewire you can start with an browser test and implement the steps you perform in your application and browser to reproduce the bug.

Unit tests should be added to the `UnitTest.php` file and browser tests should be added to `BrowserTest.php`. If one or both of these files do not exist you can create them yourself.

**Unit test**

```php
use Tests\TestCase;

class UnitTest extends TestCase  
{  
    /** @test */  
    public function livewire_can_run_action(): void  
    {
    }
}
```

**Browser test**

```php
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase  
{  
    /** @test */  
    public function livewire_can_run_action()  
    {
        // ...
    }
}
```

> [!tip]
> Explore existing Unit and Browser tests and learn how tests are written.

## Bug fix/feature development

Now it's time to start working on your bug fix or new feature.

### Create a branch

To start working on a new feature or fix a bug, you should always create a new branch in your fork with the name of your feature or fix.

> [!tip]
> Always create a new branch for your feature or fix.

Do not use your main branch of your fork as maintainers cannot modify PR's submitted from a main branch on a fork.

> [!warning]
> Any PR's submitted from a master/main branch will be closed.

### Add failing tests

The next step is to add failing tests for your code. Livewire has both Dusk browser tests and standard PHPUnit unit tests, which can be found throughout the `src/` directory respectively.

Most Livewire features all have their dedicated directory containing the code for a specific feature but it also includes the unit and browser tests for that specific feature.

For example, lets say you are adding some new functionality to Livewire's form objects, in that case, you will add your tests to `src/Features/SupportFormObjects/UnitTest.php`. Please be aware that some existing features may only contain unit or browser tests. If you can't find an existing `UnitTest.php` or `BrowserTest.php` you can go ahead and create the test yourself.

If you are building an entirely new feature for which you think none of the existing tests apply you can create a new `SupportYourFeature` directory and place your tests here.

Livewire runs both PHP and Javascript code, so Dusk browser tests are preferred to ensure everything works as expected, and can be supported with unit tests as required.

See below for an example of how a Livewire Dusk test should be structured:

```php
/** @test */
public function it_can_run_foo_action
{
    Livewire::visit(new class extends Component {
        public $count = 0;
        
        public function inc() { $this->count++; }

        public function render() { return <<<'HTML'
        <div>
            <h1>Count: <span dusk="count">{{ $count }}</span>
            <button wire:click="inc" dusk="inc">inc</button>
        </div>
        HTML;
    })
        ->assertSeeIn('@count', 0)
        ->waitForLivewire()
        ->click('@inc')
        ->assertSeeIn('@count', 1);
}
```

You can see how to use Dusk in the [Laravel documentation](https://laravel.com/docs/dusk) as well as look at
Livewire's existing browser tests for further examples.

### Add working code

Livewire has both PHP and javascript code, which you can find in the `src` directory for PHP and the `js` directory for javascript.

Change the code as required to fix the bug or add the new feature, but try to keep changes to a minimum. Consider splitting into multiple PR's if required.

> [!warning]
> PR's that make too many changes or make unrelated changes may be closed.

If you have updated any of Livewire's javascript code, you will need to recompile the assets.
To do this run `npm run build`, or you may start a watcher with `npm run watch`.

Compiled javascript assets should be committed with your changes.

> [!tip]
> If you update any javascript, make sure to recompile assets and commit them.

Once you have finished writing your code, do a review to ensure you haven't left any debugging code and formatting matches the existing style.

### Run tests

The final step before submitting is to run all tests to ensure your changes haven't impacted anything else.

To do this, run `phpunit` and confirm everything is running ok.

```shell
./vendor/bin/phpunit
```

If the Dusk browser tests don't run, see [Run tests](#setup-run-tests) in the Setup section above for more details

### Submit PR

Once all tests pass, then push your branch up to GitHub and submit your PR.

In your PR description make sure to provide a small example of what your PR does along with a thorough description of the improvement and reasons why it's useful.

Add links to any issues or discussions that are relevant for further details.

> [!tip]
> For first-time contributors, tests won't run automatically, so they will need to be started by a maintainer.

### Thanks for contributing! 🙌

And that's it!

Maintainers will review your PR and give feedback as required.

Thanks for contributing to Livewire!
