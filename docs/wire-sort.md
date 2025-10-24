
Livewire provides powerful drag-and-drop sorting capabilities through the `wire:sort` and `wire:sort:item` directives. With these tools, you can make lists of elements sortable with smooth animations—all handled for you out of the box.

## Basic usage

To make a list sortable, add `wire:sort` to the parent element and specify a method name to handle the sort event. Then, add `wire:sort:item` to each child element with a unique identifier.

Here's a basic example of a sortable todo list:

```php
<?php

use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public Todo $todo;

    public function sortItem($item, $position)
    {
        $item = $this->todo->items()->findOrFail($item);

        // Update the item's position in the database and re-order other items...
    }
};
```

```blade
<ul wire:sort="sortItem">
    @foreach ($todo->items as $item)
        <li wire:sort:item="{{ $item->id }}">
            {{ $item->title }}
        </li>
    @endforeach
</ul>
```

When a user drags and drops an item to a new position, Livewire will call your `sortItem` method with two parameters: the item's unique identifier (from `wire:sort:item`) and the new zero-based position in the list.

You are responsible for persisting the new order in your database. This typically involves updating the position of the moved item and adjusting the positions of other affected items.

## Sorting across groups

If you have multiple sortable lists on a page and want to allow users to drag items between them, you can use `wire:sort:group` to create shared groups.

By assigning the same group name to multiple sortable containers, items can be dragged from one list to another:

```php
<?php

use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public User $user;

    public function sortItem($item, $position)
    {
        $item = $this->todo->items()->findOrFail($item);

        // Update the item's position in the database and re-order other items...
    }
};
```

```blade
<div>
    @foreach ($user->todoLists as $todo)
        <ul wire:sort="sortItem" wire:sort:group="todos">
            @foreach ($todo->items as $item)
                <li wire:sort:item="{{ $item->id }}">
                    {{ $item->title }}
                </li>
            @endforeach
        </ul>
    @endforeach
</div>
```

When an item is dragged to a different group, only the handler of the destination group will receive the sort event. Your handler will need to detect that the item belongs to a different parent, re-associate it with the new parent model, and update the sort positions for both the old and new parent's items.

## Sort handles

By default, users can drag an item by clicking and dragging anywhere on the sortable element. However, you can restrict dragging to a specific handle by using `wire:sort:handle`.

This is useful when you have interactive elements within your sortable items and want to prevent accidental drags:

```blade
<ul wire:sort="sortItem">
    @foreach ($todo->items as $item)
        <li wire:sort:item="{{ $item->id }}">
            <div wire:sort:handle>
                <!-- Drag icon... -->
            </div>

            {{ $item->title }}
        </li>
    @endforeach
</ul>
```

Now users can only drag items by clicking and dragging the element marked with `wire:sort:handle`.

## Ignoring elements

You can prevent specific areas within a sortable item from triggering drag operations by using `wire:sort:ignore`. This is particularly useful when you have buttons or other interactive elements inside sortable items:

```blade
<ul wire:sort="sortItem">
    @foreach ($todo->items as $item)
        <li wire:sort:item="{{ $item->id }}">
            {{ $item->title }}

            <div wire:sort:ignore>
                <button type="button">Edit</button>
            </div>
        </li>
    @endforeach
</ul>
```

Clicking and dragging within an element marked with `wire:sort:ignore` will have no effect, allowing users to interact with buttons and other controls without accidentally triggering a sort operation.
