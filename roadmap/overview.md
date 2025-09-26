
Livewire 4 is a new version of Livewire focused on making livewire faster, more stable, and more intuitive.

## V4 Checklist
- [x] Blaze
- [x] Route::livewire()
- [x] wire:ref
- [x] CSP-safe
- [ ] Support wire:model on dialog and popover elements
- [ ] Interceptors
- [ ] data-loading
- [ ] Slots
- [ ] Attributes
- [ ] Getters/setter properties
- [x] Multi-file components
- [x] Single file components
- [ ] Islands
- [ ] Streaming
- [x] Make command
- [x] Convert commands to Prompts

### Deliverables for each feature:
- [ ] Tests
- [ ] Benchmarks
- [ ] Refactored code
- [ ] Docs

## Potential extras
- [ ] [Add tailwind merge](tailwind-merge.md)
- [ ] [Add streaming improvements](streaming.md)
- [ ] [Naked @script tags](naked-scripts.md)
- [ ] [Set `this` to `$wire` for `<scripts>`](this-wire.md)
- [ ] [Add `wire:submit.clear`](wire-submit-dot-clear.md)
- [ ] [Add `bootstrap/livewire.php` configuration file](configuration.md)
- [ ] [Add `artisan livewire:install` command](install-command.md)
- [ ] [Support multiple file uploads in S3](multiple-file-uploads-s3.md)
- [ ] [Fix component not found error](no-component-not-found.md)
- [ ] [Smart `wire:key`s](smart-keys.md)
- [ ] [Release tokens](release-tokens.md)
- [ ] [wire:navigate fixes](fix-wire-navigate.md)
- [ ] [Remove $this-> demand for computed properties](remove-this-arrow-for-computeds.md)
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
- [ ] [Fix array update hook calls and values](array-update-hook.md)
- [ ] [Plan for Volt messaging now that it's kinda baked in]
* [ ] [Static methods]
* [ ] [access to original request object]
* [Story: some story for repeating fields]
* [Story: some story for better toasts and modals]
* [Story: property assignment type-error]
* [Add server-sent events support some way somehow](sse.md)

## Breaking changes
* /livewire/upload-file -> livewire/upload
* /livewire/prefiew-file -> livewire/preview
* Make #[On] not global by default
* `this` in script is now $wire instead of window