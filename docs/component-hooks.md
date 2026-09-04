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

## Enforcing a rule before an action runs

The `call()` hook runs before every action on every component. Calling the `$returnEarly` argument prevents the action from running. This makes it possible to enforce rules across the whole application from a single place.

For example, the following hook prevents a user without an active subscription from calling the `create` method on any component:

```php
<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Livewire\ComponentHook;

class RequireSubscription extends ComponentHook
{
    public function call($method, $params, $returnEarly)
    {
        if ($method !== 'create') {
            return;
        }

        if (Auth::user()?->subscribed()) {
            return;
        }

        $returnEarly(
            $this->component->redirect(route('subscription'))
        );
    }
}
```

When an unsubscribed user triggers `create` on any component, the hook redirects them to the subscription page and the action never runs.

## Handling exceptions

The `exception()` hook lets a Component Hook intercept exceptions thrown inside any component. Livewire passes the exception and a `$stopPropagation` callable; calling `$stopPropagation()` marks the exception as handled so it is not re-thrown to Laravel's exception handler.

```php
<?php

namespace App;

use App\Exceptions\SubscriptionExpiredException;
use Livewire\ComponentHook;

class HandleSubscriptionErrors extends ComponentHook
{
    public function exception($e, $stopPropagation)
    {
        if (! $e instanceof SubscriptionExpiredException) {
            return;
        }

        $this->component->dispatch('notify', 'Your subscription has expired.');

        $stopPropagation();
    }
}
```

Register the hook in the same way:

```php
// App\Providers\AppServiceProvider.php

Livewire::componentHook(HandleSubscriptionErrors::class);
```
