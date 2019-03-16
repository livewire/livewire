# Quickstart

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

_Note: These files are generated automatically for you if you ran `php artisan make:auth` when creating your Laravel app._

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

Now, let's register a route to render our component. In your `web.php` file, you can register the route like so:

**routes/web.php**
```
Route::livewire('/counter', App\Http\Livewire\Counter::class);
```

Now, if you visit the `/counter` endpoint in your browser, you should see a blank page (we haven't added anything to the blade view yet). Let's add some basic counting functionality to our component.

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
