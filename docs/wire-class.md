Livewire's `wire:class` directive allows you to dynamically add or remove CSS classes on an element based on the result of an expression.

The `wire:class` directive evaluates expressions against your Livewire component's properties and applies the resulting classes without requiring a server round-trip. This makes it perfect for reactive UI updates that respond instantly to user interactions.

## Basic usage

Here's a practical example of using `wire:class` to style a notification component based on its severity level:

```php
use Livewire\Component;

class Notification extends Component
{
    public $type = 'info'; // 'success', 'warning', 'error', 'info'
    public $message = '';

    public function setType($type)
    {
        $this->type = $type;
    }
}
```

```blade
<div>
    <div class="p-4 rounded-lg border" 
         wire:class="{
             'bg-green-50 border-green-500 text-green-900': type === 'success',
             'bg-yellow-50 border-yellow-500 text-yellow-900': type === 'warning',
             'bg-red-50 border-red-500 text-red-900': type === 'error',
             'bg-blue-50 border-blue-500 text-blue-900': type === 'info'
         }">
        <strong>{{ $message }}</strong>
    </div>

    <div class="mt-4 space-x-2">
        <button wire:click="setType('success')">Success</button>
        <button wire:click="setType('warning')">Warning</button>
        <button wire:click="setType('error')">Error</button>
        <button wire:click="setType('info')">Info</button>
    </div>
</div>
```

When different buttons are clicked, the notification dynamically changes its color scheme and styling based on the `$type` property value.

## Class object syntax

You can use an object syntax where the keys are class names and the values are boolean expressions:

```blade
<div wire:class="{ 'bg-red-500': hasError, 'bg-green-500': isSuccess }">
    Status message
</div>
```

In this example, the `bg-red-500` class is applied when `hasError` is true, and `bg-green-500` is applied when `isSuccess` is true.

## Multiple conditions

You can combine multiple conditions using ternary operators for more complex class logic:

```blade
<button wire:class="active ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'">
    Click me
</button>
```

## Negation

You can negate a property by prefixing it with `!`:

```blade
<div wire:class="!collapsed ? 'expanded' : 'collapsed'">
    Content area
</div>
```

## Working with existing classes

The `wire:class` directive works alongside regular `class` attributes. Static classes remain unchanged while dynamic classes are added or removed:

```blade
<div class="p-4 rounded" wire:class="highlighted ? 'border-2 border-blue-500' : ''">
    This div always has padding and rounded corners
</div>
```

The `wire:class` directive provides a clean, declarative way to manage dynamic classes in your Livewire components without writing custom JavaScript or making unnecessary server requests.
