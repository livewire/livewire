---
Title: Quickstart
Order: 1
---

```toc
min_depth: 1
```

<a name="quickstart"></a>
# Quickstart guide

Livewire provides a powerful way to create dynamic, interactive user interfaces using only PHP and Blade templates.

The best way to learn is by doing, so in this guide, we're going to create a simple "counter" Livewire component and render it in the browser. Most applications have no use for a "counter" component, but it's a great way to experience Livewire for the first time as it takes advantage of Livewire's "live"ness while not being more complicated than it needs to be.

# Prerequisites

Before we start, make sure you have the following installed:

- Laravel version 8 or later
- PHP version 8.1 or later

# Step 1: Install Livewire

Install Livewire into your Laravel application using Composer:

```shell
composer require livewire/livewire
```

# Step 2: Create a Livewire component

Create a new Livewire component using the `make:livewire` Artisan command:

```shell
php artisan make:livewire Counter
```

This will create a new Livewire component called `Counter` in the `app/Http/Livewire` directory along with its corresponding Blade view file: `resources/views/livewire/counter.blade.php`.

# Step 3: Edit the Livewire component

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

# Step 4: Edit the Blade view

Open the `resources/views/livewire/counter.blade.php` file and replace its content with the following code:

```html
<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
```

This code will display the `$count` property and two buttons that increment and decrement the `$count` property respectively.

# Step 5: Add the Livewire component to a route

Open the `routes/web.php` file and add the following code:

```php
use App\Http\Livewire\Counter;

Route::get('/counter', Counter::class);
```

Above, we've registered a new route that points to the `Counter` Livewire component.

# Step 6: Create a Blade layout for Livewire

Before can visit `/counter` in the browser, we need an HTML layout for our component to render inside of. By default, Livewire will automatically look for a layout file called `resources/views/components/layout.blade.php`.

Create this file by running the following command:

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

Our component will be rendered in place of the `$slot` variable, and Livewire will automatically inject any JavaScript assets it needs when we load the page.

Now that we're all set up, our component is ready to test out!

# Step 7: Test it out

Visit `/counter` in your browser and you should see a number displayed on the screen with two buttons to increment and decrement the number.

Click on the buttons to increment and decrement the number. You will notice that the count updates in real-time without the page reloading. This is the magic of Livewire.

We just scratched the surface of what Livewire is capable of. You can either keep reading along with the documentation, or follow one of our in-depth tutorials that walk you through building real-life applications.

Cheers!

* More Docs
* Real-life tutorials

