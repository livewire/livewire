
Livewire provides drag-and-drop sorting through the `wire:sort` directive. Add it to a parent element and use `wire:sort:item` on each child to make lists sortable with smooth animations out of the box.

## Basic usage

To make a list sortable, add `wire:sort` to the parent element with a handler method name, and `wire:sort:item` to each child with a unique identifier:

```php
<?php

use Livewire\Component;

new class extends Component {
    public TodoList $list;

    public function handleSort($id, $position)
    {
        $task = $this->list->tasks()->findOrFail($id);

        // Update the task's position and re-order other tasks...
    }
};
```

```blade
<ul wire:sort="handleSort">
    @foreach ($list->tasks as $task)
        <li wire:key="{{ $task->id }}" wire:sort:item="{{ $task->id }}">
            {{ $task->title }}
        </li>
    @endforeach
</ul>
```

When a user drags and drops an item to a new position, Livewire will call your handler with two parameters: the item's identifier (from `wire:sort:item`) and the new zero-based position.

You are responsible for persisting the new order in your database.

## Sorting across groups

To allow dragging items between multiple lists, use `wire:sort:group` with the same group name on each container.

To identify which group the item was dropped into, add `wire:sort:group-id` to each container. Its value will be passed as a third parameter to your handler:

```php
<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Card;

new class extends Component {
    public Board $board;

    #[Computed]
    public function columns()
    {
        return $this->board->columns;
    }

    public function handleSort($id, $position, $columnId)
    {
        $card = $this->board->cards()->findOrFail($id);

        // Update the card's position and re-order other cards...
    }
};
```

```blade
<div>
    @foreach ($this->columns as $column)
        <ul wire:sort="handleSort" wire:sort:group="cards" wire:sort:group-id="{{ $column->id }}">
            @foreach ($column->cards as $card)
                <li wire:key="{{ $card->id }}" wire:sort:item="{{ $card->id }}">
                    {{ $card->title }}
                </li>
            @endforeach
        </ul>
    @endforeach
</div>
```

When an item is dragged to a different group, only the destination group's handler fires.

## Sort handles

By default, users can drag an item by clicking anywhere on it. To restrict dragging to a specific handle, use `wire:sort:handle`:

```blade
<ul wire:sort="handleSort">
    @foreach ($list->tasks as $task)
        <li wire:key="{{ $task->id }}" wire:sort:item="{{ $task->id }}">
            <div wire:sort:handle>
                <!-- Drag icon... -->
            </div>

            {{ $task->title }}
        </li>
    @endforeach
</ul>
```

Now users can only initiate a drag from the handle element.

## Ignoring elements

To prevent specific areas from triggering drag operations, use `wire:sort:ignore`. This is useful for buttons or other interactive elements inside sortable items:

```blade
<ul wire:sort="handleSort">
    @foreach ($list->tasks as $task)
        <li wire:key="{{ $task->id }}" wire:sort:item="{{ $task->id }}">
            {{ $task->title }}

            <div wire:sort:ignore>
                <button type="button">Edit</button>
            </div>
        </li>
    @endforeach
</ul>
```

## Reference

```blade
wire:sort="method"
wire:sort:item="id"
wire:sort:group="name"
wire:sort:group-id="identifier"
wire:sort:handle
wire:sort:ignore
```

This directive has no modifiers.
