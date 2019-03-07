# Quickstart

// Im stuck here
A) List out explicit instructions to create app.blade.php
B) Just use the component syntax
C) Make assumptions about the Laravel developer's skill

// Also, I should change this to just a plain Hello World, no Counter. But have a section with Examples: Counter, Todo, Contacts

Now that you have everything installed, run `php artisan livewire:make Counter --view` to generate a new Livewire Component (`app/Http/Livewire/Counter.php`), and it's corresponding view (`resources/views/livewire/counter.blade.php`).

**Component**
```php
class Counter extends LivewireComponent
{
    public function render()
    {
        return view('livewire.counter');
    }
}
```

**View**
```
<div>
    {{-- Go effing nuts. --}}
</div>
```

Before we can render it, make sure you have an `app` layout stored in `resources/views/layouts/app.blade.php` that yields a section called `content`.

Two quick notes:
1. These files are generated automatically for you if you ran `php artisan make:auth` when creating your Laravel app
2. If your layout file or Blade section is called something else, you can configure that easily. Take a look [here](docs/rendering_components.md) for instructions.

**resources/views/layouts/app.blade.php**
```
<html>
    ...
    <body>
        @yield('content')
        ...
    </body>
</html>
```

Now, we can register a route for our component. In your `web.php` file, register a route like so:

**routes/web.php**
```
Route::livewire('/counter', App\Http\Livewire\Counter::class);
```

Before we check it out in the browser, let's add some simple functionality to our component:

**App\Http\Livewire\Counter.php**
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
**resources/views/livewire/counter.blade.php**
```
<div>
    {{ $count }}
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
```

Now browse to `/counter` in your browser, and with any luck you should see your `Counter` component rendered. If you click the "+" or "-" button, the page should automatically update without a page reload.

Hopefully the power of Livewire is starting to become apparent to you, if not, keep reading through the docs to dive a little deeper.
