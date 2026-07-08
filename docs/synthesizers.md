
Because Livewire components are dehydrated (serialized) into JSON, then hydrated (unserialized) back into PHP components between requests, their properties need to be JSON-serializable.

Natively, PHP serializes most primitive values into JSON easily. However, in order for Livewire components to support more sophisticated property types (like models, collections, carbon instances, and stringables), a more robust system is needed.

Therefore, Livewire provides a point of extension called "Synthesizers" that allow users to support any custom property types they wish.

> [!tip] Make sure you understand hydration first
> Before using Synthesizers, it's helpful to fully understand Livewire's hydration system. You can learn more by reading the [hydration documentation](/docs/4.x/hydration).

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

The dehydrated JSON representing this component's state now contains a [metadata tuple](/docs/4.x/hydration#deeply-nested-tuples) instead of a plain empty string:

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

Every synth must contain a static `$key` property that Livewire uses to convert a [metadata tuple](/docs/4.x/hydration#deeply-nested-tuples) like `['', { s: 'str' }]` back into a stringable. As you may notice, each metadata tuple has an `s` key referencing this key.

Inversely, when Livewire is dehydrating a property, it will use the synth's static `match()` function to identify if this particular Synthesizer is a good candidate to dehydrate the current property (`$target` being the current value of the property):

```php
public static function match($target)
{
    return $target instanceof Stringable;
}
```

If `match()` returns true, the `dehydrate()` method will be used to take the property's PHP value as input and return the JSONable [metadata](/docs/4.x/hydration#deeply-nested-tuples) tuple:

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

## JavaScript synthesizers

Synthesizers make rich values like Carbon dates usable on the server, but by default, JavaScript only ever sees the raw dehydrated value. For example, given the following component:

```php
use Carbon\Carbon;

class ShowPost extends Component
{
    public Carbon $publishedAt;
}
```

Accessing `$wire.publishedAt` in JavaScript returns a plain ISO date string like `"2021-01-01T00:00:00+00:00"` — not something you can call date methods on.

JavaScript synthesizers are the client-side counterpart to PHP synthesizers. By registering one for the same synth key, the raw value is upgraded into a rich JavaScript object when component state reaches the frontend, and converted back to its raw wire format when updates are sent to the server.

Register a JavaScript synthesizer using `Livewire.synth()` inside a `livewire:init` listener so it's available before components initialize:

```js
document.addEventListener('livewire:init', () => {
    Livewire.synth('cbn', {
        match: (value) => value instanceof Date,

        hydrate: (value, meta) => new Date(value),

        dehydrate: (value) => value.toISOString(),
    })
})
```

With this synth registered, every Carbon property is a real `Date` object on the frontend:

```blade
<span x-text="$wire.publishedAt.toLocaleDateString()"></span>

<button x-on:click="$wire.publishedAt = new Date()">Publish now</button>
```

When you assign a fresh `Date` and the next request is sent, Livewire calls `dehydrate()` and the server receives the ISO string, which the PHP `CarbonSynth` hydrates back into a Carbon instance.

Let's break down the three required functions:

The first parameter of `Livewire.synth()` is the key of the PHP synthesizer you are pairing with — the same key found in the `s` field of the property's [metadata tuple](/docs/4.x/hydration#deeply-nested-tuples) (`cbn` is the key of Livewire's internal Carbon synthesizer).

`hydrate()` receives the raw wire value along with its full metadata tuple and returns the rich JavaScript value. It is called whenever component state arrives from the server: on the initial page load and after every subsequent request.

```js
hydrate: (value, meta) => new Date(value),
```

`dehydrate()` does the inverse: it receives the rich value and must return the raw wire format. It is called when Livewire compares component state and builds the update payload for the server. Whatever it returns must be a value the PHP synthesizer's `hydrate()` method understands — typically the same format the server sent down in the first place.

```js
dehydrate: (value) => value.toISOString(),
```

`match()` identifies rich values inside component state. Livewire uses it to know which values should be treated as atomic units when diffing, and which synthesizer should dehydrate a brand-new instance you've assigned (like the `new Date()` above, which never came from the server).

```js
match: (value) => value instanceof Date,
```

### Pairing with a custom PHP synthesizer

JavaScript synthesizers really shine when paired with a custom PHP synthesizer. Continuing the `Address` example from above, you can give the frontend a matching rich object:

```js
class Address {
    constructor({ street, city, state, zip }) {
        this.street = street
        this.city = city
        this.state = state
        this.zip = zip
    }

    get full() {
        return `${this.street}, ${this.city}, ${this.state} ${this.zip}`
    }
}

document.addEventListener('livewire:init', () => {
    Livewire.synth('address', {
        match: (value) => value instanceof Address,

        hydrate: (value) => new Address(value),

        dehydrate: (value) => ({
            street: value.street,
            city: value.city,
            state: value.state,
            zip: value.zip,
        }),
    })
})
```

Now the same `Address` concept exists on both sides of the wire:

```blade
<span x-text="$wire.address.full"></span>
```

You can still mutate individual fields (`$wire.address.street = '...'` or `wire:model="address.street"`) — when a rich value changes, Livewire sends its entire dehydrated form to the server, where the PHP synthesizer hydrates it back into an `Address`.

### Things to know

* **Registration is global per key.** Registering a synth for `cbn` upgrades _every_ Carbon and native date property across your entire application, so make sure your frontend code is prepared for that.
* **Rich values are atomic.** Livewire never diffs _inside_ a rich value. When one changes, its full dehydrated form is sent to the server and the corresponding property update fires at the property's path.
* **Rich values also work as action parameters.** Calling `$wire.save(new Date())` dehydrates the parameter before it's sent to the server.
* **Binding inputs directly to a rich property** (`wire:model="publishedAt"`) sets the input's raw string value onto the property. The string is sent to the server as-is and re-hydrated there, but the input will display the browser's string representation of the rich object — for form inputs, prefer binding to nested fields or handling conversion yourself.

For reference, these are the keys of Livewire's built-in synthesizers: `arr` (arrays), `cbn` (Carbon/DateTime), `clctn` (Collections), `str` (Stringables), `enm` (Enums), `std` (stdClass), `mdl` (Eloquent models), `elcln` (Eloquent collections), `wrbl` (Wireables), `form` (Form objects), and `fil` (file uploads).
