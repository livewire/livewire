
Warning: Livewire 3.0 is still in beta and although we'll try our best to minimize breaking changes, they are possible.

## Breaking changes

* `wire:model` changes
* Event system changes
* Eloquent model changes
* Livewire uses Alpine

## Step-by-Step Upgrade Guide

### Upgrade PHP

Livewire now requires version 8.1 or greater

### Update composer

```
"livewire/livewire": "v2.x", [tl! -]
"livewire/livewire": "v3.x", [tl! +]
```

```shell
composer update livewire/livewire
```

### Update dependancies

* Ignition
* Filament
* Wire Elements

### Clear artisan cache

```shell
artisan view:clear
```

### Merge new configuration

- "asset_url" (in favor of the run-time one)
- "app_url" (in favor of the new run-time one)

### Move Livewire directory

app/Http/Livewire -> app/Livewire

### New component layout file default

- Previous: `resources/views/layouts/app.blade.php` | New: `resources/views/components/layouts/app.blade.php`

### Alpine
- Remove any Alpine CDN scripts or Alpine npm imports and use the one Livewire provides
- The following Alpine plugins are already bundled so you can remove these CDN or npm imports:

```
Alpine.plugin(morph)
Alpine.plugin(history)
Alpine.plugin(intersect)
Alpine.plugin(collapse)
Alpine.plugin(focus)
Alpine.plugin(persist)
Alpine.plugin(navigate)
```

### `wire:model`
- Change `wire:model.defer` to `wire:model`
- Change `wire:model.lazy` to `wire:model.blur`
- Change `wire:model` to `wire:model.live`

### `@entangle`
- Change `@entangle(...).defer` to `@entangle`
- Change `@entangle(...)` to `@entangle.live`

### Events
- Change `$this->emit()` and `$emit` to `$this->dispatch()` and `$dispatch()`
    - Same with `emitTo()`
    - Add parameter names
- Change `dispatchBrowserEvent()` to `dispatch()`
    - Change array syntax to named param syntax to match ->emit()
- Remove the concept of "up" ($emitUp or ->emitUp)
- "assertEmitted" -> "assertedDispatched"

### QueryString
- Replace by default now, add "push" to keep the same
- "except" is no longer needed

### Pagination
- Republish pagination views if you have previously published them.
- Can no longer access `$page` directly -> `$paginators['page']` or `getPage()`

### `wire:click.prefetch`

Removed

### Component class

- The component ID is no longer a public property ($id), please use $this->id() or $this->getId() to get the component id.
- Can no longer use same names for properties and methods

### JavaScript

* prepend `$` to everything (`$watch`, `$upload`, etc...)
* Changed lifecycle hooks
* Removed page expired hook
* 'livewire:load' => 'livewire:init'

### Eloquent models

- model binding has been disabled
* You must set the config "livewire.model_binding" to true

### Localization

Livewire 2 included support for a locale prefix.

In Livewire 3 this automatic prefix has been removed. Instead, you will need to add a custom Livewire update route to your `routes/web.php` file inside your route group that applies localization.

For example, here is how you would use a custom Livewire update route along with the `mcamara/laravel-localization` package:

```php
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::prefix(LaravelLocalization::setLocale())
    ->group(function () {
        ... // Your other localized routes.

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('livewire/update', $handle);
        });
    });
```

See [[installation#Configuring Livewire's update endpoint]] for more details on creating a custom Livewire update endpoint.

---

## `wire:submit.prevent` no longer needed
- Change `wire:submit.prevent` to `wire:submit`
