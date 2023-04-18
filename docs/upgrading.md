### Pagination
- Remove `Livewire\WithPagination` trait from components as it's no longer needed to get pagination to work.
- Republish pagination views if you have previously published them.

## Localisation
In version 2 there was support for a locale prefix.

In V3 this automatic prefix has been removed and instead, you will need to add a custom Livewire update route to your `routes/web.php` file inside your route group that applies localization.

Below is an example of how you would use a custom Livewire update route along with `mcamara/laravel-localization` package.

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
