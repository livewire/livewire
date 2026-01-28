To include Livewire components in a Laravel package, you'll need to register them in your package's service provider.

## Single-file and multi-file components

For single-file (SFC) and multi-file (MFC) components, use `addNamespace` in your service provider's `boot()` method:

```php
use Livewire\Livewire;

public function boot(): void
{
    Livewire::addNamespace(
        namespace: 'mypackage',
        viewPath: __DIR__ . '/../resources/views/livewire',
    );
}
```

This registers all SFC and MFC components in your package's `resources/views/livewire` directory under the `mypackage` namespace.

**Usage:**

```blade
<livewire:mypackage::counter />
<livewire:mypackage::users.table />
```

## Class-based components

For class-based components, you'll need to provide additional parameters and register your views with Laravel:

```php
use Livewire\Livewire;

public function boot(): void
{
    Livewire::addNamespace(
        namespace: 'mypackage',
        classNamespace: 'MyVendor\\MyPackage\\Livewire',
        classPath: __DIR__ . '/Livewire',
        classViewPath: __DIR__ . '/../resources/views/livewire',
    );

    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'my-package');
}
```

Your component's `render()` method should reference the view using Laravel's package namespace syntax:

```php
public function render()
{
    return view('my-package::livewire.counter');
}
```

**Usage:**

```blade
<livewire:mypackage::counter />
```

## File naming

The ⚡ emoji prefix used in Livewire component filenames can cause issues with Composer when publishing packages. For package development, avoid using the bolt emoji in your component filenames—use `counter.blade.php` instead of `⚡counter.blade.php`.
