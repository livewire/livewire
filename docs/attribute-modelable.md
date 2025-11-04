The `#[Modelable]` attribute designates a property in a child component that can be bound to from a parent component using `wire:model`.

## Basic usage

Apply the `#[Modelable]` attribute to a property in a child component to make it bindable:

```php
<?php // resources/views/components/⚡todo-input.blade.php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    #[Modelable] // [tl! highlight]
    public $value = '';
};
?>

<div>
    <input type="text" wire:model="value">
</div>
```

Now the parent component can bind to this child component just like any other input element:

```php
<?php // resources/views/components/⚡todos.blade.php

use Livewire\Component;

new class extends Component
{
    public $todo = '';

    public function addTodo()
    {
        // Use $this->todo here...
    }
};
?>

<div>
    <livewire:todo-input wire:model="todo" /> <!-- [tl! highlight] -->

    <button wire:click="addTodo">Add Todo</button>
</div>
```

When the user types in the `todo-input` component, the parent's `$todo` property automatically updates.

## How it works

Without `#[Modelable]`, you would need to manually handle two-way communication between parent and child:

```php
// Without #[Modelable] - manual approach
<livewire:todo-input
    :value="$todo"
    @input="todo = $event.value"
/>
```

The `#[Modelable]` attribute simplifies this by allowing `wire:model` to work directly on the component.

## Building reusable input components

`#[Modelable]` is perfect for creating custom input components that feel like native HTML inputs:

```php
<?php // resources/views/components/⚡date-picker.blade.php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    #[Modelable]
    public $date = '';
};
?>

<div>
    <input
        type="date"
        wire:model="date"
        class="border rounded px-3 py-2"
    >
</div>
```

```blade
{{-- Usage in parent --}}
<livewire:date-picker wire:model="startDate" />
<livewire:date-picker wire:model="endDate" />
```

## Modifiers

The parent can use wire:model modifiers, and they'll work as expected:

```blade
{{-- Live updates on every keystroke --}}
<livewire:todo-input wire:model.live="todo" />

{{-- Update on blur --}}
<livewire:todo-input wire:model.blur="todo" />

{{-- Debounce updates --}}
<livewire:todo-input wire:model.live.debounce.500ms="todo" />
```

## Example: Custom rich text editor

Here's a more complex example of a rich-text editor component:

```php
<?php // resources/views/components/⚡rich-editor.blade.php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    #[Modelable]
    public $content = '';
};
?>

<div>
    <div
        x-data="{
            content: $wire.entangle('content')
        }"
        x-init="
            // Initialize your rich text editor library here
            editor.on('change', () => content = editor.getContent())
        "
    >
        <!-- Rich text editor UI -->
    </div>
</div>
```

```blade
{{-- Usage --}}
<livewire:rich-editor wire:model="postContent" />
```

## Limitations

> [!warning] Only one modelable property per component
> Currently Livewire only supports a single `#[Modelable]` attribute per component, so only the first one will be bound.

## When to use

Use `#[Modelable]` when:

* Creating reusable input components (date pickers, color pickers, rich text editors)
* Building form components that need to work with `wire:model`
* Wrapping third-party JavaScript libraries as Livewire components
* Creating custom inputs with special validation or formatting

## Learn more

For more information about parent-child communication and data binding, see the [Nesting Components documentation](/docs/4.x/nesting#binding-to-child-data-using-wiremodel).
