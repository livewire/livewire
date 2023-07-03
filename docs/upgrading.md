## Prerequisites
- Run `artisan view:clear`

## Alpine is now included
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
    - Add parameter names
- Change `dispatchBrowserEvent()` to `dispatch()`
    - Change array syntax to named param syntax to match ->emit()
- Remove the concept of "up" ($emitUp or ->emitUp)
- "assertEmitted" -> "assertedDispatched"

## New component layout file default
- Previous: `resources/views/layouts/app.blade.php` | New: `resources/views/components/layouts/app.blade.php`

## Pagination
- Republish pagination views if you have previously published them.
- Can no longer access `$page` directly -> `$paginators['page']` or `getPage()`

## Remove wire:click.prefetch

### The component ID is no longer a public property ($id), please use $this->id() or $this->getId() to get the component id.

## Localization
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

## Extras
- Can no longer use same names for properties and methods


## Config modifications
- "asset_url" (in favor of the run-time one)
- "app_url" (in favor of the new run-time one)
