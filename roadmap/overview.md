
Livewire 4 is a new version of Livewire focused on making livewire faster, more stable, and more intuitive.

## V4 Checklist
- [x] Blaze
- [x] Route::livewire()
- [x] wire:ref
- [x] CSP-safe
- [ ] Support wire:model on dialog and popover elements
- [x] Interceptors
- [x] data-loading
- [x] Slots
- [x] Attributes
- [ ] Getters/setter properties
- [x] Multi-file components
- [x] Single file components
- [x] Islands
- [x] Streaming
- [x] Make command
- [x] Convert commands to Prompts

## Potential extras
- [ ] [Add `wire:submit.clear`](wire-submit-dot-clear.md)
- [ ] [Add `bootstrap/livewire.php` configuration file](configuration.md)
- [ ] [Add `artisan livewire:install` command](install-command.md)
- [ ] [Support multiple file uploads in S3](multiple-file-uploads-s3.md)
- [ ] [Remove $this-> demand for computed properties](remove-this-arrow-for-computeds.md)
- [ ] [Dispatch from mount()](dispatch-from-mount.md)
- [ ] [Fill empty X-Livewire request headers](fill-request-headers.md)
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
* [Story: some story for repeating fields]
* [Story: property assignment type-error]
* [Add server-sent events support some way somehow](sse.md)

## Breaking changes
* /livewire/upload-file -> livewire/upload
* /livewire/prefiew-file -> livewire/preview
* Make #[On] not global by default
* `this` in script is now $wire instead of window