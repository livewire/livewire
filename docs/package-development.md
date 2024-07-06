## Registering Custom Components

You may manually register components using the Livewire::component method. This can be useful if you want to provide Livewire components from a composer package. Typically this should be done in the boot method of a service provider.

```php
class YourPackageServiceProvider extends ServiceProvider {
    public function boot() {
        Livewire::component('some-component', SomeComponent::class);
    }
}
```

Now, applications with your package installed can consume your component in their views like so:

```blade
<div>
    @livewire('some-component')
</div>
```
