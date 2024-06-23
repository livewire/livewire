## Global Component Hooks

In cases where you want to add features or behavior to every single component in your application, you can use Livewire "Component Hooks".

Component Hooks allow you to define a single class with the ability to hook in to a Livewire component's lifecycle externally (not on the component class itself, and not in a trait).

Before we look at an actual example of using them, here's a generic Component Hook class showing every available method you can use inside them:

```php
use Livewire\ComponentHook;

class MyComponentHook extends ComponentHook
{
    public static function provide()
    {
        // Runs once at application boot.
        // Can be used to register any services you may need.
    }

    public function mount($params, $parent)
    {
        // Called when a component is "mounted"
        // 
        // $params: Array of parameters passed into the component
        // $parent: The parent component object if this is a nested component
    }

    public function hydrate($memo)
    {
        // Called when a component is "hydrated"
        //
        // $memo: An associative array of the "dehydrated" metadata for this component
    }

    public function boot()
    {
        // Called when the component boots
    }

    public function update($property, $path, $value)
    {
        // Called before the component updates...

        return function () {
            // Called after the component property has updated...
        };
    }

    public function call($method, $params, $returnEarly)
    {
        // Called before a method on the component is called...

        return function ($returnValue) {
            // Called after a method is called
        };
    }

    public function render($view, $data)
    {
        // Called after "render" is called but before the Blade has been rendered...
        return function ($html) {
            // Called after the component's view has been rendered
        };
    }

    public function dehydrate($context)
    {
        // Called when a component "dehydrates"
    }

    public function exception($e, $stopPropagation)
    {
        // Called if an exception is thrown within a component...
    }
}
```

You can register a Component Hook from a service provider like your `App\Providers\AppServiceProvider` like so:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::componentHook(MyComponentHook::class);
    }

    // ...
}
```

Now that you've seen the broad overview of Component Hooks, here's a more practical example of using them to provide useful functionality for your application.

Let's say you wanted to support the ability to return a CSV from any Livewire action, and it would automatically trigger a file download. For example, you could return a Csv from a method called `save` inside a `CreatePost` component:

```php
use Livewire\Component;

class CreateUser extends Component
{
    public $username = '';

    public $email = '';

    public function something()
    {
        return new Csv();
    }

    // ...
}
```


```php
<?php

namespace App;

use Livewire\ComponentHook;

class SupportCsvDownloads extends ComponentHook
{
    public function call($method, $params, $returnEarly)
    {
        // Called before a method on the component is called...

        return function ($returnValue) {
            if ($returnValue instanceof Csv) {
                // do something
            }
        };
    }
}
```

You can 
