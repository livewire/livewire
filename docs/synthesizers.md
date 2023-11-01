
Because Livewire components are dehydrated (serialized) into JSON, then hydrated (unserialized) back into PHP components between requests, their properties need to be JSON-serializable.

Natively, PHP serializes most primitive values into JSON easily. However, in order for Livewire components to support more sophisticated property types (like models, collections, carbon instances, and stringables), a more robust system is needed.

Therefore, Livewire provides a point of extension called "Synthesizers" that allow users to support any custom property types they wish.

> [!tip] Make sure you understand hydration first
> Before using Synthesizers, it's helpful to fully understand Livewire's hydration system. You can learn more by reading the [hydration documentation](/docs/hydration).

## Understanding Synthesizers

Before exploring the creation of custom Synthesizers, let's first look at the internal Synthesizer that Livewire uses to support [Laravel Stringables](https://laravel.com/docs/strings).

Suppose your application contained the following `CreatePost` component:

```php
class CreatePost extends Component
{
    public $title = '';
}
```

Between requests, Livewire might serialize this component's state into a JSON object like the following:

```js
state: { title: '' },
```

Now, consider a more advanced example where the `$title` property value is a stringable instead of a plain string:

```php
class CreatePost extends Component
{
    public $title = '';

    public function mount()
    {
        $this->title = str($this->title);
    }
}
```

The dehydrated JSON representing this component's state now contains a [metadata tuple](/docs/hydration#deeply-nested-tuples) instead of a plain empty string:

```js
state: { title: ['', { s: 'str' }] },
```

Livewire can now use this tuple to hydrate the `$title` property back into a stringable on the next request.

Now that you've seen the outside-in effects of Synthesizers, here is the actual source code for Livewire's internal stringable synth:

```php
use Illuminate\Support\Stringable;

class StringableSynth extends Synth
{
    public static $key = 'str';

    public static function match($target)
    {
        return $target instanceof Stringable;
    }

    public function dehydrate($target)
    {
        return [$target->__toString(), []];
    }

    public function hydrate($value)
    {
        return str($value);
    }
}
```

Let's break this down piece by piece.

First is the `$key` property:

```php
public static $key = 'str';
```

Every synth must contain a static `$key` property that Livewire uses to convert a [metadata tuple](/docs/hydration#deeply-nested-tuples) like `['', { s: 'str' }]` back into a stringable. As you may notice, each metadata tuple has an `s` key referencing this key.

Inversely, when Livewire is dehydrating a property, it will use the synth's static `match()` function to identify if this particular Synthesizer is a good candidate to dehydrate the current property (`$target` being the current value of the property):

```php
public static function match($target)
{
    return $target instanceof Stringable;
}
```

If `match()` returns true, the `dehydrate()` method will be used to take the property's PHP value as input and return the JSONable [metadata](/docs/hydration#deeply-nested-tuples) tuple:

```php
public function dehydrate($target)
{
    return [$target->__toString(), []];
}
```

Now, at the beginning of the next request, after this Synthesizer has been matched by the `{ s: 'str' }` key in the tuple, the `hydrate()` method will be called and passed the raw JSON representation of the property with the expectation that it returns the full PHP-compatible value to be assigned to the property.

```php
public function hydrate($value)
{
    return str($value);
}
```

## Registering a custom Synthesizer

To demonstrate how you might author your own Synthesizer to support a custom property, we will use the following `UpdateProperty` component as an example:

```php
class UpdateProperty extends Component
{
    public Address $address;

    public function mount()
    {
        $this->address = new Address();
    }
}
```

Here's the source for the `Address` class:

```php
namespace App\Dtos\Address;

class Address
{
    public $street = '';
    public $city = '';
    public $state = '';
    public $zip = '';
}
```

To support properties of type `Address`, we can use the following Synthesizer:

```php
use App\Dtos\Address;

class AddressSynth extends Synth
{
    public static $key = 'address';

    public static function match($target)
    {
        return $target instanceof Address;
    }

    public function dehydrate($target)
    {
        return [[
            'street' => $target->street,
            'city' => $target->city,
            'state' => $target->state,
            'zip' => $target->zip,
        ], []];
    }

    public function hydrate($value)
    {
        $instance = new Address;

        $instance->street = $value['street'];
        $instance->city = $value['city'];
        $instance->state = $value['state'];
        $instance->zip = $value['zip'];

        return $instance;
    }
}
```

To make it available globally in your application, you can use Livewire's `propertySynthesizer` method to register the synthesizer from your service provider boot method:

```php
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::propertySynthesizer(AddressSynth::class);
    }
}
```

## Supporting data binding

Using the `UpdateProperty` example from above, it is likely that you would want to support `wire:model` binding directly to properties of the `Address` object. Synthesizers allow you to support this using the `get()` and `set()` methods:

```php
use App\Dtos\Address;

class AddressSynth extends Synth
{
    public static $key = 'address';

    public static function match($target)
    {
        return $target instanceof Address;
    }

    public function dehydrate($target)
    {
        return [[
            'street' => $target->street,
            'city' => $target->city,
            'state' => $target->state,
            'zip' => $target->zip,
        ], []];
    }

    public function hydrate($value)
    {
        $instance = new Address;

        $instance->street = $value['street'];
        $instance->city = $value['city'];
        $instance->state = $value['state'];
        $instance->zip = $value['zip'];

        return $instance;
    }

    public function get(&$target, $key) // [tl! highlight:8]
    {
        return $target->{$key};
    }

    public function set(&$target, $key, $value)
    {
        $target->{$key} = $value;
    }
}
```
