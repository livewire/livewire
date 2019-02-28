# Livewire Docs

Livewire is a VueJs inspired front-end framework for Laravel that will blow your freaking mind.

# Quickstart
## Installation
`composer require calebporzio/livewire`

Add `{!! Livewire::scripts() !!}` to your `app.blade.php` file.

## Setting up your first component
Run `artisan livewire:make Counter` to generate a new Livewire Component here: `app/Http/Livewire/Counter.php`.

Note: you can optionally add the `—view` flag to generate a corresponding view in: `resources/views/livewire/counter.blade.php`

You should now have a component that looks like this:
```
class Counter extends LivewireComponent
{
    public function render()
    {
        return view('livewire.counter');
    }
}
```

And a view that looks like:
```
<div>
    {{-- Go effing nuts. --}}
</div>
```

You can render it in a view using the following directive:
```
@extends('layouts.app')

@section('content')
<div>
    @livewire(App\Http\Livewire\Counter::class)
</div>
@endsection
```

## Let’s manage some state
In Livewire, state is stored in your component as class properties.

Let’s create a state to store the current count of our counter component:
```
class Counter extends LivewireComponent
{
    public $count = 0;

    public function render()
    {
        return view('livewire.counter');
    }
}
```

Component properties are made available in the blade view automatically. We can display that state in our view just like we would in a traditional blade view:
```
<div>
    {{ $count }}
</div>
```

## Mutating state
Let’s add `+` and `-` buttons to our view, that we will wire up to our component.
```
<div>
    {{ $count }}
    <button>+</button>
    <button>-</button>
</div>
```

Now let’s add “increment” and “decrement” behavior to our component:
```
class Counter extends LivewireComponent
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

Now, let’s wire that new behavior up in our view:
```
<div>
    {{ $count }}
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
```

Viola! Livewire will handle all the behind the scenes magic. All you have to know is that when a user clicks on one of those buttons, the appropriate methods will be called on your component, and the entire thing will be re-rendered without a page load. Pretty cool huh?


# Binding Data
You can bind the value of `<input>` elements to your component properties just like you would in VueJs with something like `v-model`. In livewire, the syntax is `wire:model`. Here is an example todo list component.

Livewire component
```
class Todos extends LivewireComponent
{
    public $todo = '';
    public $todos = [];

    public function addTodo()
    {
        $this->todos[] = $this->todo;
        $this->todo = '';
    }

    public function render()
    {
        return view('livewire.todos');
    }
)
```

Component view (`livewire/todos.blade.php`)
```
<div>
    <input type="text" wire:model="todo">
    <button wire:click="addTodo">Add Todo</button>

    <ul>
    @foreach ($todos as $todo)
       <li>{{ $todo }}</li>
    @endforeach
    </ul>
</div>
```


## TODO
* 2 options: the directive OR the route macro (routing) (route-model binding)
* binding data `wire:model`
* listening for events`wire:click`, `wire:submit`, `wire:keydown`
	* adding `prevent`, `stop`, `min.250ms`
* loading, transitions
* validating
* redirecting
* nesting! (emiting, passing data in)
* testing
* Turbolinks
* setting a custom prefix
* drivers
