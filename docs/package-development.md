## Registering Custom Components

You may manually register components using the `Livewire::component` method. This can be useful if you want to provide Livewire components from a composer package. Typically, this should be done in a service provider's `register` method.

```php
class YourPackageServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register() {
        Livewire::component('your-component', YourComponent::class);
    }
}
```

Now, applications with your package installed can consume your component in their views like so:

```blade
<div>
    <livewire:your-component />
</div>
```
