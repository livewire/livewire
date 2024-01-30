Hi there and welcome to the Livewire contribution guide. In this guide we are going to take a look at how you can contribute back to Livewire by submitting new features, failing tests, or bug fixes.
## Setting up Livewire and Alpine locally
In order to contribute back the easiest way to do this is by ensuring the Livewire and Alpine repositories are setup on your machine locally to easily make changes and run the test suite.
#### Forking and cloning the repositories
To get up and running the first step is to fork and clone the repositories. The easiest is to use the [Github CLI](https://cli.github.com/) but if you can also do this manually by clicking the "Fork" button on the Github  [repository page](https://github.com/livewire/livewire).

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

To setup Alpine make sure you have [NPM](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) installed and run the following commands (to manually fork visit the [repository page](https://github.com/alpinejs/alpine)):

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
#### 1. Determine where to add your test
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

#### 2. Determine the type of test
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

#### 3. Preparing your pull request branch
Once you've completed your feature or failing test it's time to submit your PR to the Livewire repository. First you need to ensure you commit your changes to a separate branch (do not use `main`). To create a new branch you can use the `git` command:

```shell
git branch my-feature
git checkout my-feature
```

You can name your branch anything you want but  for future reference it's easier to describe your feature or failing test.

Next, commit your changes to your branch, you can use `git add .` to stage all changes followed by `git commit -m "Add my feature"` to commit all changes and add the commit message describing what was committed.

Almost there, right now your branch is only available on your machine. To create a PR we need to ensure it's pushed to our forked Livewire repository using `git push`.

```shell
git push origin my-feature

Enumerating objects: 13, done.
Counting objects: 100% (13/13), done.
Delta compression using up to 8 threads
Compressing objects: 100% (6/6), done.

To github.com:Username/livewire.git
 * [new branch]        my-feature -> my-feature
```

#### 4. Submitting your pull request
We are almost there! Open your browser and navigate to the forked Livewire repository (`https://github.com/<your-username>/livewire`). In the center of your screen you will see a new notification **my-feature had recent pushes 1 minute ago** and a button **Compare & pull request**. Click the button to open the pull request form.

Enter a title that describes your pull request and continue to the description. The text area already contains an pre-defined template. Try to answer every question:

```
Review the contribution guide first at: https://livewire.laravel.com/docs/contribution-guide

1️⃣ Is this something that is wanted/needed? Did you create a discussion about it first?
Yes, you can find the discussion here: https://github.com/livewire/livewire/discussions/999999

2️⃣ Did you create a branch for your fix/feature? (Main branch PR's will be closed)
Yes, the branch is named `my-feature`

3️⃣ Does it contain multiple, unrelated changes? Please separate the PRs out.
No, the changes are only related to my feature.

4️⃣ Does it include tests? (Required)
Yes

5️⃣ Please include a thorough description (including small code snippets if possible) of the improvement and reasons why it's useful.

These changes will improve memory usage. You can see the benchmark results here:

// ...

```

All done? Hit **Create pull request**  🚀 Congrats! You've created your first contribution 🎉

Maintainers will review your PR and sometimes give feedback or request changes. Please try to address feedback as soon as possible.

Thanks for contributing to Livewire!
