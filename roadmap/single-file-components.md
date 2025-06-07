
Currently in Livewire v3 all components are normal PHP classes that extend \Liveiwre\Component like so:

## V3 components

```php
<?php // Path: app/Livewire/Counter.php

namespace App\Livewire;

class Counter extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

Then the associated view looks like this:

```php
// Path resources/views/livewire/counter.blade.php

<div>
    Count: {{ $count }}

    <button wire:click="increment">Increment</button>
</div>
```

In V4, we will be transitioning to a new single-file, view-first, system to unify this experience.

Here is the new, V4 way:

## V4 components

Note: Components will be resolved similar to anonymous Blade components. Instead of only being inside the resources/views/livewire directory, they will co-exist among other blade files in resources/views/components.

Here is the single file version of the above counter component. Would likely be a file called:

- resources/views/components/counter.wire.php

(notice the new file extension for these single file components (wire.php))

```php
@php
new class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>
    Count: {{ $count }}

    <button wire:click="increment">Increment</button>
</div>
```

### External components

Alternatively, if folks still want seperate files they would reference the class from this view like so:

```php
@php(new \App\Livewire\Components\Counter)

<div>
    Count: {{ $count }}

    <button wire:click="increment">Increment</button>
</div>
```

This way we can still maintain a view-first approach but allow for seperate of concerns for those that value that.
