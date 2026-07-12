Livewire's `wire:for` directive renders a list of elements by looping over a component property — entirely on the client, without a server round-trip.

It is the Livewire equivalent of Alpine's [`x-for`](https://alpinejs.dev/directives/for) directive, evaluated against your component's properties instead of Alpine data.

Unlike Blade's `@foreach`, which renders the list on the server, `wire:for` renders in the browser and re-renders instantly whenever the underlying property changes — whether from a server update or a client-side mutation like `$wire.items.push(...)`.

Because the loop contents serve as a repeatable template, `wire:for` must be used on a `<template>` tag:

```blade
<template wire:for="item in items" wire:for:key="item.id">
    <div>...</div>
</template>
```

The `<template>` tag itself is never displayed — an element is rendered directly after it for every item in the list.

## Basic usage

Here's an example of rendering a list of tasks:

```php
<?php

use Livewire\Component;

new class extends Component {
    public $tasks = [
        ['id' => 1, 'title' => 'Write the docs'],
        ['id' => 2, 'title' => 'Ship the feature'],
    ];

    public function remove($id)
    {
        $this->tasks = array_values(
            array_filter($this->tasks, fn ($task) => $task['id'] !== $id)
        );
    }
};
```

```blade
<div>
    <ul>
        <template wire:for="task in tasks" wire:for:key="task.id">
            <li>
                <span wire:text="task.title"></span>

                <button wire:click="remove(task.id)">Remove</button>
            </li>
        </template>
    </ul>
</div>
```

Inside the loop, the iterated item (`task` above) is available to any Livewire or Alpine directive — `wire:text`, `wire:click`, `x-show`, and so on. Notice how `wire:click="remove(task.id)"` passes the current item's id back to the server action.

> [!info] Single root element
> Like `x-for`, the `<template>` tag must contain a single root element.

## Keying items

Always provide a key so the list can be re-ordered and updated efficiently without recreating every element. Use `wire:for:key` with an expression that uniquely identifies each item:

```blade
<template wire:for="task in tasks" wire:for:key="task.id">
```

Alpine's `:key` syntax is also supported and behaves identically:

```blade
<template wire:for="task in tasks" :key="task.id">
```

If no key is provided, items are keyed by their index in the list.

> [!warning] wire:for:key, not wire:key
> Elsewhere in Livewire, `wire:key` holds a static string rendered by Blade. A template loop needs a *per-item expression* instead, which is why the key lives on a dedicated attribute. Using `wire:key` on a `wire:for` template logs a console warning and is otherwise ignored.

## Accessing the index

Like `x-for`, you can destructure the loop's index alongside the item:

```blade
<template wire:for="(task, index) in tasks" wire:for:key="task.id">
    <li wire:text="(index + 1) + '. ' + task.title"></li>
</template>
```

## Nesting loops

`wire:for` templates can be nested — inner loops can reference the outer loop's item:

```blade
<template wire:for="column in columns" wire:for:key="column.id">
    <div>
        <h2 wire:text="column.title"></h2>

        <template wire:for="card in column.cards" wire:for:key="card.id">
            <p wire:text="card.title"></p>
        </template>
    </div>
</template>
```

## `wire:for` vs. `@foreach`

Blade's `@foreach` renders on the server: the items are part of the initial HTML, and updating the list requires a network round-trip and re-render. `wire:for` renders in the browser: updates apply instantly from client-side state.

Because the content of `wire:for` is rendered on the client, it isn't present in the initial server-rendered HTML. If the list must be visible to search engines or before JavaScript loads, use `@foreach` instead.

## Reference

```blade
<template wire:for="item in items" wire:for:key="item.id">
    <div>...</div>
</template>
```

Supported expression forms:

```blade
wire:for="item in items"
wire:for="(item, index) in items"
```

Key each item with `wire:for:key="expression"` (or Alpine's `:key`).

This directive has no modifiers and must be used on a `<template>` tag containing a single root element.
