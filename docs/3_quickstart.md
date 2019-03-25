# Quickstart

**Notice: You must first follow the [Installation instructions](/livewire/docs/installation) if you haven't yet.**

## Generate component

Run `php artisan livewire:make Counter` to generate a new Livewire Component (`app/Http/Livewire/Counter.php`), and it's corresponding view (`resources/views/livewire/counter.blade.php`).

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

## Set up Blade layout file

Before we continue, make sure you have an `app` layout stored in `resources/views/layouts/app.blade.php` that yields a section called `content`.

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

## Register component's route

In your `routes/web.php` file, you can register the component route like so:

**routes/web.php**
```
Route::livewire('/counter', App\Http\Livewire\Counter::class);
```

Now, if you visit the `/counter` endpoint in your browser, you should see a blank page (we haven't added anything to the blade view yet).

## Add "Counter" functionality

 Let's add some basic counting functionality to our component. Replace the generated content of your Counter component and view with the following:

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

**resources/views/livewire/counter.blade.php**
```
<div>
    {{ $count }}
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
```

## View it in the browser

Now navigate to `/counter` in your browser. With any luck, you should see the `Counter` component rendered. If you click the "+" or "-" button, the page should automatically update without a page reload.
