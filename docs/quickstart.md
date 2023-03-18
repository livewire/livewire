---
Title: Quickstart
Order: 1
---

<a name="quickstart"></a>
# Quickstart Guide

Livewire provides a powerful way to create dynamic, interactive user interfaces using only PHP and Blade templates. In this quickstart guide, you'll learn how to create a Livewire component and leverage its real-time communication with your server to build dynamic, responsive web applications with ease.

## Prerequisites

Before you start, make sure you have the following installed:

- Laravel version 8 or later
- PHP version 8.1 or later

## Step 1: Install Livewire

Install Livewire using Composer:

```shell
composer require livewire/livewire
```

## Step 2: Create a Livewire Component

Create a new Livewire component using the `make:livewire` Artisan command:

```shell
php artisan make:livewire Counter
```

This will create a new Livewire component called `Counter` in the `app/Http/Livewire` directory along with its corresponding Blade view file in the `resources/views/livewire` directory.

## Step 3: Edit the Livewire Component

Open the `app/Http/Livewire/Counter.php` file and replace its content with the following code:

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

In this code, we have defined a Livewire component called `Counter` with a public property `$count` initialized to `0`. We have also defined two methods called `increment` and `decrement` which increment and decrement the `$count` property respectively. Finally, we have defined a `render` method that returns a view called `livewire.counter`.

## Step 4: Edit the Blade View

Open the `resources/views/livewire/counter.blade.php` file and replace its content with the following code:

```html
<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
```

This code will display the `$count` property and two buttons that increment and decrement the `$count` property respectively.

## Step 5: Add the Livewire Component to a Route

Open the `routes/web.php` file and add the following code:

```php
use App\Http\Livewire\Counter;

Route::get('/counter', Counter::class);
```

This code will create a new route that points to our `Counter` Livewire component.

## Step 6: Create a Blade Layout for Livewire

To render your Livewire component in the browser, you need to create a base layout for it. Livewire will look for a layout file called `resources/views/components/layout.blade.php`.

Create this file by running the following command:

```shell
php artisan livewire:layout
```

This command generates a basic layout file with the following HTML structure:

```html
<html>
	<head>
		<title>{{ $title ?? 'Livewire Quickstart' }}</title>
	</head>

	<body>

		<!-- // -->

		{{ $slot }}

	</body>
</html>
```

This layout file includes an HTML structure with a `<head>` tag for the page title and a `<body>` tag for the component's content. The `$title` variable is used to set the page title and can be overridden in individual components.

## Step 7: Test it out!

Visit `/counter` in your browser and you should see a number displayed on the screen with two buttons to increment and decrement the number.

Click on the buttons to increment and decrement the number. You will notice that the number on the screen updates in real-time without the page reloading. This is the magic of Livewire.

We just scratched the surface of what Livewire is capable of. You can either keep reading along with the documentation, or follow one of our in-depth tutorials that walk you through building real-life applications:

* More Docs
* Real-life tutorials

