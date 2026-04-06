# Project Overview

Livewire is a full-stack framework for Laravel. It allows you to build dynamic front-end applications without leaving the comfort of PHP/Laravel/Blade.

It is similar to projects like Phoenix Liveview however it isn't "live" in that it doesn't use real-time connections like websockets. Just simple http requests.

It renders on the server and uses morphing algorithm to path the DOM with updates from the server.

It "dehdrates" the state of a component into a JS-consumable object that is passed back to the server to be "hydrated" during the next request.

# Tech Stack

- Laravel + PHP + Vanilla JS
- JS Bundling: esbuild

# Project Structure

```
src/           → The PHP source files
- Features/    → Individual ~modules to isolate features
js/            → The JavaScript source files
tests/         → A smattering of PHP tests however, most tests are inside their src/Feature directories
```

# Development Workflow

## Setup

Run `composer setup` to install all dependencies (PHP, JS, ChromeDriver).

## Build JS assets

Run `npm run build` to bundle the JS

**IMPORTANT:** Do not commit `dist/` files. They are built in CI.

# Code Patterns

- Write JavaScript without semicolons
- Always use `let` (no `const`)
- Strive for single-word features and affordances (ex. `wire:click.stop` instead of `wire:click.stop-propagation`)

# Testing

Most browser and unit tests live inside of feature directories for example:

src/Features/SupportSlots/[UnitTest.php|BrowserTest.php]

```bash
composer test                                        # all tests
composer test:unit                                   # unit tests only
composer test:browser                                # browser tests (headless)
composer test:browser:headed                         # browser tests (opens Chrome)
composer test:browser -- --filter="SupportCSP"       # specific browser tests
```

**IMPORTANT:** Never run the full Livewire browser test suite — it takes too long. Always use `--filter` to run only the specific tests relevant to your changes. The full suite runs in CI.

# Releasing

## Bumping Alpine (optional)

If you need to update the Alpine.js dependency before a release:

```bash
npm run bump-alpine
```

Then commit the updated `package.json` and `package-lock.json`.

## Creating a release

1. Go to **Actions → "Release" → "Run workflow"**
2. Enter the version number (e.g. `4.3.0` — the `v` prefix is added automatically)
3. The workflow installs deps, builds assets, runs JS tests, creates a tag with the built `dist/` files, and creates a GitHub Release with auto-generated notes
