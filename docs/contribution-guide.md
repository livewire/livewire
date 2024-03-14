Hi there and welcome to the Livewire contribution guide. In this guide, we are going to take a look at how you can contribute to Livewire by submitting new features, fixing failing tests, or resolving bugs.

## Setting up Livewire and Alpine locally
To contribute, the easiest way is to ensure that the Livewire and Alpine repositories are set up on your local machine. This will allow you to make changes and run the test suite with ease.

### Forking and cloning the repositories
To get started, the first step is to fork and clone the repositories. The easiest way to do this is by using the [GitHub CLI](https://cli.github.com/), but you can also perform these steps manually by clicking the "Fork" button on the GitHub [repository page](https://github.com/livewire/livewire).

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

To set up Alpine, make sure you have [NPM](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) installed, and then run the following commands. If you prefer to fork manually, you can visit the [repository page](https://github.com/alpinejs/alpine).

```shell
# Fork and clone Alpine
gh repo fork alpinejs/alpine --default-branch-only --clone=true --remote=false -- alpine

# Switch the working directory to alpine
cd alpine

# Install all npm dependencies
npm install

# Build all Alpine packages
npm run build

# Link all Alpine packages locally
cd alpine/packages/alpinejs && npm link
cd alpine/packages/anchor && npm link
cd alpine/packages/collapse && npm link
cd alpine/packages/csp && npm link
cd alpine/packages/docs && npm link
cd alpine/packages/focus && npm link
cd alpine/packages/history && npm link
cd alpine/packages/intersect && npm link
cd alpine/packages/mask && npm link
cd alpine/packages/morph && npm link
cd alpine/packages/navigate && npm link
cd alpine/packages/persist && npm link

# Switch the working directory back to livewire
cd ../livewire

# Link all packages
npm link alpinejs @alpinejs/anchor @alpinejs/collapse @alpinejs/csp @alpinejs/docs @alpinejs/focus @alpinejs/history @alpinejs/intersect @alpinejs/mask @alpinejs/morph @alpinejs/navigate @alpinejs/persist

# Build Livewire
npm run build
```

## Contributing a Failing Test

If you're encountering a bug and are unsure about how to solve it, especially given the complexity of the Livewire core, you might be wondering where to start. In such cases, the easiest approach is to contribute a failing test. This way, someone with more experience can assist in identifying and fixing the bug. Nonetheless, we do recommend that you also explore the core to gain a better understanding of how Livewire operates.

Let's take a step-by-step approach.

#### 1. Determine where to add your test
The Livewire core is divided into different folders, each corresponding to specific Livewire features. For example:

```shell
src/Features/SupportAccessingParent
src/Features/SupportAttributes
src/Features/SupportAutoInjectedAssets
src/Features/SupportBladeAttributes
src/Features/SupportChecksumErrorDebugging
src/Features/SupportComputed
src/Features/SupportConsoleCommands
src/Features/SupportDataBinding
//...
```

Try to locate a feature that is related to the bug you are experiencing. If you can't find an appropriate folder or if you're unsure about which one to select, you can simply choose one and mention in your pull request that you require assistance with placing the test in the correct feature set.

#### 2. Determine the type of test
The Livewire test suite consists of two types of tests:

1. **Unit tests**: These tests focus on the PHP implementation of Livewire.
2. **Browser tests**: These tests run a series of steps inside a real browser and assert the correct outcome. They mainly focus on the Javascript implementation of Livewire.

If you're unsure about which type of test to choose or if you're unfamiliar with writing tests for Livewire, you can start with a browser test. Implement the steps you perform in your application and browser to reproduce the bug.

Unit tests should be added to the `UnitTest.php` file, and browser tests should be added to `BrowserTest.php`. If one or both of these files do not exist, you can create them yourself.

**Unit test**

```php
use Tests\TestCase;

class UnitTest extends TestCase
{
    /** @test */
    public function livewire_can_run_action(): void
    {
       // ...
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

> [!tip] Not sure how to write tests?
> You can learn a lot by explore existing Unit and Browser tests to learn how tests are written. Even copying and pasting an existing test is a great starting point for writing your own test.

#### 3. Preparing your pull request branch
Once you have completed your feature or failing test, it's time to submit your Pull Request (PR) to the Livewire repository. First, ensure that you commit your changes to a separate branch (avoid using `main`). To create a new branch, you can use the `git` command:

```shell
git checkout -b my-feature
```

You can name your branch anything you want, but for future reference, it's helpful to use a descriptive name that reflects your feature or failing test.

Next, commit your changes to your branch. You can use `git add .` to stage all changes and then `git commit -m "Add my feature"` to commit all changes with a descriptive commit message.

However, your branch is currently only available on your local machine. To create a Pull Request, you need to push your branch to your forked Livewire repository using `git push`.

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
We're almost there! Open your web browser and navigate to your forked Livewire repository (`https://github.com/<your-username>/livewire`). In the center of your screen, you will see a new notification: "**my-feature had recent pushes 1 minute ago**" along with a button that says "**Compare & pull request**." Click the button to open the pull request form.

In the form, provide a title that describes your pull request and then proceed to the description section. The text area already contains a predefined template. Try to answer every question:

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

All set? Click on **Create pull request** 🚀 Congratulations! You've successfully created your first contribution 🎉

The maintainers will review your PR and may provide feedback or request changes. Please make an effort to address any feedback as soon as possible.

Thank you for contributing to Livewire!
