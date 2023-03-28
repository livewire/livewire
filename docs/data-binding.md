---
Title: Data Binding
Order: 4
---

* Introduction (introduction to binding with `wire:model`)
* Live binding with `wire:model.live`
* Deferred binding with `wire:model.defer`
* Lazy binding with `wire:model.lazy`
* Debouncing input `wire:model.debounce`
* Throttling input with `wire:model.throttle`
* Binding to nested values
* `wire:model` binding to nested Livewire component values
* Binding to eloquent model properties (this is no longer supported in v3 because it was prone to missunderstandings and performance and security implications, but you can enable this old behavior with a livewire.php config item)
* Using the "updating" and "updated" lifecycle hooks
* Security concerns
	* Every property is `wire:model`able, even if there is no `wire:model` for it

```toc
allow_inconsistent_headings: true
min_depth: 1
max_depth: 6
```

Data binding is a fundamental concept in Livewire that allows you to create a two-way connection between component properties and form input elements. In this documentation page, we'll discuss various data binding techniques, including `wire:model`, updated hooks, `$wire.set()`, and more.

# Basic Data Binding

To bind a property to an input element, use the `wire:model` directive. This creates a two-way binding that automatically updates the property value when the input changes.

```php
<!-- example-component.blade.php -->
<div>
    <label for="name">Name:</label>
    <input type="text" id="name" wire:model="name">
    <p>Your name is: {{ $name }}</p>
</div>
```

### Debounce and Lazy Data Binding

By default, Livewire updates the property value and re-renders the component with every input change. However, you can control the update frequency using `wire:model.debounce` or `wire:model.lazy`.

**Debounce**: Specify a debounce duration to delay updates and limit the number of updates per second. This is useful for reducing server requests when working with rapidly changing input values, like typing in a search field.

```php
<input type="text" wire:model.debounce.300ms="search">
```

**Lazy**: Update the property value only when the input element loses focus (e.g., when the user tabs away or clicks outside the input). This is useful for reducing the number of updates during data entry.

```php
<input type="text" wire:model.lazy="email">
```

### Updated Hooks

Livewire provides updated hooks that allow you to execute actions when a property value changes. To define an updated hook, create a method in your component with the format `updated{PropertyName}`.

```php
use Livewire\Component;

class ExampleComponent extends Component
{
    public $name;

    public function updatedName($newValue)
    {
        // Perform an action after the name property is updated
    }

    // ...
}
```

### Nested Properties

You can bind to nested properties within objects or arrays using the dot notation.

```php
<!-- Bind to a nested object property -->
<input type="text" wire:model="user.name">

<!-- Bind to a nested array property -->
<input type="text" wire:model="users.0.name">
```

### Binding Arrays

Livewire supports binding to array properties. For example, you can bind a series of checkboxes to an array property:

### Binding to Boolean Properties

You can bind to boolean properties using checkboxes or radio buttons. When the input is checked, the property value will be `true`, and when unchecked, it will be `false`.

```php
<!-- example-component.blade.php -->
<div>
    <label><input type="checkbox" wire:model="is_active"> Is Active</label>

</div>
```

### Using `$wire.set()`

In addition to `wire:model`, you can use the `$wire.set()` method to update a component property from JavaScript. This is useful when you want to set a property value in response to a JavaScript event or interaction.

First, ensure that you have the `@livewireScripts` directive in your layout:

```html
<!-- layouts/app.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <!-- ... -->
</head>
<body>
    <!-- ... -->
    @livewireScripts
</body>
</html>

```

Then, you can use `$wire.set()` in your JavaScript code to update a property value. For example, to update the `name` property when a button is clicked:

```php
<!-- example-component.blade.php -->
<div>
    <button onclick="setName()">Set Name</button>
    <p>Your name is: {{ $name }}</p>
</div>

<script>
    function setName() {
        window.livewire.find('component-id').set('name', 'New Name');
    }
</script>
```

Replace `'component-id'` with the actual component ID, which can be accessed using `$this->id` in your Livewire component.