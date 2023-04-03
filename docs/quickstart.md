Livewire provides a powerful way to create dynamic, interactive, user interfaces using only PHP and Blade templates.

The best way to learn is by doing, so in this guide, we're going to create a simple "counter" Livewire component and render it in the browser. Most applications have no use for a "counter" component, but it's a great way to experience Livewire for the first time as it demonstrates Livewire's "liveness" in the simplest way possible.

## Prerequisites

Before we start, make sure you have the following installed:

- Laravel version 9 or later
- PHP version 8.1 or later

## Install Livewire

From the root directory of your application, run the following Composer command:

```shell
composer require livewire/livewire
```

## Create a Livewire component

Livewire provides a conventient Artisan command to make new components quickly:

```shell
php artisan make:livewire Counter
```

This will create a new Livewire component called `Counter` in the `app/Http/Livewire` directory along with its corresponding Blade view: `resources/views/livewire/counter.blade.php`

## Edit the Livewire component

Open `app/Http/Livewire/Counter.php` and replace its content with the following code:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Counter extends Component
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

As you can see, we now have a public property called `$count` that Livewire will use to track the counter state. We also have two methods or "actions" that manipulate the count, and finally we have a `render()` method where return a Blade view for our component to render.

## Edit the Blade view

Open the `resources/views/livewire/counter.blade.php` file and replace its content with the following code:

```html
<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
```

This code will display the `$count` property and two buttons that increment and decrement the `$count` property respectively.

## Register a route for the component

Open the `routes/web.php` file and add the following code:

```php
use App\Http\Livewire\Counter;

Route::get('/counter', Counter::class);
```

As you can see, Livewire components can be treated as controllers when registering routes. Under the hood, each component is also a [single-action controller](https://laravel.com/docs/10.x/controllers#single-action-controllers).

## Create a Blade layout for Livewire

Before you can visit `/counter` in the browser, we need an HTML layout for our component to render inside of. By default, Livewire will automatically look for a layout file called: `resources/views/components/layout.blade.php`

Create this file if, it doesn't already exist, by running the following command:

```shell
php artisan livewire:layout
```

This command generates a basic layout file with the following structure:

```html
<html>
	<head>
		<title>{{ $title ?? 'Page Title' }}</title>
	</head>

	<body>

		<!-- // -->

		{{ $slot }}

	</body>
</html>
```

Our "Counter" component will be rendered in place of the `$slot` variable in the above template.

You may notice there is no JavaScript or CSS provided by Livewire. That is because version 3 and above automatically injects the front-end assets it needs.

Now that we're all set up, our component is ready to test out!

## Test it out

Visit `/counter` in your browser and you should see a number displayed on the screen with two buttons to increment and decrement the number.

Click on the buttons to increment and decrement the number. You will notice that the count updates in real-time without the page reloading. This is the magic of Livewire.

We just scratched the surface of what Livewire is capable of. You can either keep reading along with the documentation, or follow one of our in-depth tutorials that walk you through building real-life applications.

Happy building!