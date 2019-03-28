# Quickstart

## Install Livewire

*Include the PHP*
```bash
> composer require calebporzio/livewire
```

*Include the JavaScript*
> Note: This will typically be added to a global layout file like `layouts/app.blade.php`
<div title="Component"><div title="Component__class"><div char="fade">

```html
    ...
```
</div>

```php
    {!! Livewire::scripts() !!}
```
<div char="fade">

```html
</body>
</html>
```
</div></div></div>

## Create a component

Run the following command to generate a new Livewire component called `Counter` and it's corresponding Blade view.

```bash
> php artisan livewire:make Counter
```

Running this command will generate the following two files:

<div title="Component">
<div title="Component__class">

app/Http/Livewire/Counter.php
```php
<?php

namespace App\Http\Livewire;

use Livewire\LivewireComponent;

class Counter extends LivewireComponent
{
    public function render()
    {
        return view('livewire.counter');
    }
}
```
</div>
<div title="Component__view">

resources/views/livewire/counter.blade.php
```html
<div>
    {{-- Go effing nuts. --}}
</div>
```
</div>
</div>

## Add functionality

To add "counting" functionality, replace the generated content of your `Counter` component and view with the following:

<div title="Component"><div title="Component__class">

Counter.php
```php
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
</div><div title="Component__view">

counter.blade.php
```html
<div style="text-align: center">
    <button wire:click="increment">+</button>
    <h1>{{ $count }}</h1>
    <button wire:click="decrement">-</button>
</div>
```
</div></div>

## Inlude in Blade view

You can now render this component inside any Blade view using the `@livewire` Blade directive.

<div title="Component"><div title="Component__class">

Any Blade View

<div char="fade">

```html
@extends('layouts.app')

@section('content')
    ...
```

</div>

```php
    @livewire(App\Http\Livewire\Counter::class)
```

<div char="fade">

```html
    ...
@endsection
```

</div>
</div></div>

## View it in the browser

Now load the page your component lives on in your browser. You should see the `Counter` component rendered. If you click the "+" or "-" button, the page should automatically update without a page reload. Magic.
