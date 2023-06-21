## Prerequisits
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

## Must now add #[Prop]
- Add `#[Prop]` to properties that inherit from the parent component

## QueryString
- Replace by default now, add "push" to keep the same
- "except" is no longer needed

## `emit()` and `dispatchBrowserEvent()` are now just `dispatch()`
- Change `$this->emit()` and `$emit` to `$this->dispatch()` and `$dispatch()`
    - Same with `emitTo()`
- Change `dispatchBrowserEvent()` to `dispatch()`
- Remove the concept of "up"
- "assertEmitted" -> "assertedDispatched"

## New component layout file default
- Previous: `resources/views/layouts/app.blade.php` | New: `resources/views/components/layouts/app.blade.php`

## Pagination
- Remove `Livewire\WithPagination` trait from components as it's no longer needed to get pagination to work.
- Republish pagination views if you have previously published them.
- Can no longer access `$page` directly -> `$paginators['page']` or `getPage()`

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
