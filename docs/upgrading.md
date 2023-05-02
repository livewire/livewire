## Prerequisits
- Run `artisan view:clear`

## Alpine is included now
- Remove any Alpine CDN scripts or Alpine npm imports and use the one Livewire provides

## `wire:model.defer` is now default
- Change `wire:model.defer` to `wire:model`
- Change `wire:model.lazy` to `wire:model.blur`
- Change `wire:model` to `wire:model.live`

## @entangle is deferred by default
- Change `@entangle(...).defer` to `@entangle`
- Change `@entangle(...)` to `@entangle.live`

## `wire:submit.prevent` no longer needed
- Change `wire:submit.prevent` to `wire:submit`

## QueryString
- Replace by default now, add "push" to keep the same
- "except" is no longer needed

## `emit()` and `dispatchBrowserEvent()` are now just `dispatch()`
- Change `$this->emit()` and `$emit` to `$this->dispatch()` and `$dispatch()`
    - Same with `emitTo()`
- Change `dispatchBrowserEvent()` to `dispatch()`

## New component layout file default
- Previous: `resources/views/layouts/app.blade.php` | New: `resources/views/components/layouts/app.blade.php`

## Pagination
- Remove `Livewire\WithPagination` trait from components as it's no longer needed to get pagination to work.
- Republish pagination views if you have previously published them.
- Can no longer access `$page` directly -> `$paginators['page']` or `getPage()`

---

## Extras
- Can no longer use same names for properties and methods
