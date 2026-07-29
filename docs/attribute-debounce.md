The `#[Debounce]` attribute allows you to define a property's `wire:model` debounce timing directly on your Livewire component. This lets you configure debounce behavior once and reuse the property without adding `.debounce.Xms` modifiers to every `wire:model` binding.

## Basic usage

Apply the `#[Debounce]` attribute to a public property. Pass the debounce duration in milliseconds:

```php
<?php // resources/views/components/⚡search-box.blade.php

use Livewire\Attributes\Debounce;
use Livewire\Component;

new class extends Component {
    #[Debounce(250)] // [tl! highlight]
    public string $search = '';
};
```

```blade
<input wire:model.live="search">
```

When the user types into the input, Livewire waits `250ms` after the last change before sending the update request.

Without the attribute, you would need to define the debounce duration directly in your Blade template:

```blade
<input wire:model.live.debounce.250ms="search">
```

The `#[Debounce]` attribute allows you to keep this configuration alongside the property it affects.

## Default debounce duration

You may omit the duration to use Livewire's default debounce timing:

```php
#[Debounce]
public string $search = '';
```

This is equivalent to:

```blade
<input wire:model.live.debounce.150ms="search">
```

## Overriding the attribute

A `wire:model` modifier in your Blade template takes precedence over the property attribute. This allows you to define a sensible default while still overriding it when needed:

```php
#[Debounce(250)]
public string $search = '';
```

```blade
<input wire:model.live.debounce.500ms="search">
```

In this example, the input uses `500ms` because the Blade modifier overrides the attribute value.

## Using with `wire:model.live`

The `#[Debounce]` attribute only controls the debounce duration. It does not make a property update live by itself.

For example:

```php
#[Debounce(500)]
public string $search = '';
```

```blade
<input wire:model="search">
```

The property will still update using Livewire's default deferred behavior. To send updates while the user types, use `wire:model.live`:

```blade
<input wire:model.live="search">
```

## Behavior

| Blade | Attribute | Result |
| - | - | - |
| `wire:model.live="search"` | `#[Debounce]` | Uses the default `150ms` debounce |
| `wire:model.live="search"` | `#[Debounce(250)]` | Uses `250ms` debounce |
| `wire:model.live.debounce.500ms="search"` | `#[Debounce(250)]` | Uses `500ms` (Blade modifier wins) |
| `wire:model="search"` | `#[Debounce(250)]` | Remains deferred |

## Form objects

The `#[Debounce]` attribute can also be applied to properties inside form objects:

```php
<?php

use Livewire\Attributes\Debounce;
use Livewire\Form;

class SearchForm extends Form
{
    #[Debounce(300)]
    public string $query = '';
}
```

```blade
<input wire:model.live="form.query">
```

## Learn more

For more information about binding data to Livewire properties, see the [wire:model](/docs/4.x/wire-model)