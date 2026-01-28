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

## Build JS assets

Run `npm run build` to bundle the JS

# Code Patterns

- Write JavaScript without semicolons
- Always use `let` (no `const`)
- Strive for single-word features and affordances (ex. `wire:click.stop` instead of `wire:click.stop-propagation`)

# Testing

Most browser and unit tests live inside of feature directories for example:

src/Features/SupportSlots/[UnitTest.php|BrowserTest.php]

Unit: Run `phpunit --testsuite="Unit" [optional test file] [optional test name]`
Browser: Run `phpunit --testsuite="Browser" [optional test file] [optional test name]`
