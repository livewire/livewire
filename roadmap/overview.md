
Livewire 4 is a new version of Livewire focused on making livewire faster, more stable, and more intuitive.

## Pre-beta
- [ ] [Add single file components](single-file-components.md)
    - [ ] [New folder structure]
    - [ ] [New ⚡ emoji file names]
- [ ] [Add tailwind merge](tailwind-merge.md)
- [ ] [Support for slots](component-slots.md)
- [ ] [Add streaming improvements](streaming.md)
- [ ] [Livewire::route() for pages](livewire-route.md)
- [ ] [Add partials](partials.md)
- [ ] [Naked @script tags](naked-scripts.md)
- [ ] [Set `this` to `$wire` for `<scripts>`](this-wire.md)
- [ ] [Blade component code folding](code-folding.md)
- [ ] [Add `wire:ref`](wire-ref.md)
- [ ] [Add `bootstrap/livewire.php` configuration file](configuration.md)
- [ ] [Add `artisan livewire:install` command](install-command.md)
- [ ] [Fix component not found error](no-component-not-found.md)
- [ ] [Smart `wire:key`s](smart-keys.md)
- [ ] [Support multiple file uploads in S3](multiple-file-uploads-s3.md)
- [ ] [Convert commands to laravel prompts](use-prompts.md)
- [ ] [Release tokens](release-tokens.md)

## Post-beta
- [ ] [wire:navigate fixes](fix-wire-navigate.md)
- [ ] [Dispatch from mount()](dispatch-from-mount.md)
- [ ] [Fill empty X-Livewire request headers](fill-request-headers.md)
- [ ] [Use navigation for pagination](navigate-pagination.md)
- [ ] [Move morph out of effects](move-morph.md)
- [ ] [Missing closing divs warning](warn-closing-elements.md)
- [ ] [Add dev modal](dev-modal.md)
- [ ] [Docs rewrite](docs-rewrite.md)
- [ ] [Docs recipe section](docs-recipes.md)
- [ ] [Docs best practices](docs-best-practices.md)
- [ ] [Missing model 404 problems](missing-models.md)
- [ ] [$wire should be actual Alpine component data](actual-alpine-component-data.md)
- [ ] [Named wire:model](wire-model-named.md)
- [ ] [Hydratable query string](hydratable-query-string-hook.md)
- [ ] [Mutable updating hook](mutable-update-hook.md)
- [ ] [Explicit hydrate/dehydrate methods](hydration-control.md)
- [ ] [wire:submit with sending form data](wire-submit-form-data.md)

## Ill-defined
* (laracon) [wire:loading / data-loading stuff?]
* (laracon) [bailable/async/parralel requests]
* (laracon) [static methods]
* (laracon) [Support for attributes](component-attributes.md)
* (laracon) [Sunset Volt](sunset-volt.md)
* [scoped polling]
* [access to original request]
* [Story: some story for repeating fields]
* [Story: some story for better toasts and modals]
* [Story: property assignment type-error]
* [#[Expose] actions]
* [Add server-sent events support some way somehow](sse.md)

## Breaking changes
* /livewire/upload-file -> livewire/upload
* /livewire/prefiew-file -> livewire/preview
* Make #[On] not global by default
* `this` in script is now $wire instead of window