## Attributes

Currently, Livewire v3 components don't support forwarding attributes like Blade components do. This makes it difficult to create reusable, composable components that can accept arbitrary HTML attributes.

In Blade components, you can easily forward attributes using the `$attributes` variable:

```blade
<!-- Blade component: alert.blade.php -->
@props(['type' => 'info'])

<div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
    {{ $slot }}
</div>
```

```blade
<!-- Usage -->
<x-alert type="error" class="mb-4" id="error-alert" data-testid="alert">
    Something went wrong!
</x-alert>
```

The `class`, `id`, and `data-testid` attributes are automatically forwarded to the root element.

## The Problem

In Livewire v3, there's no equivalent mechanism. If you want to pass CSS classes or other HTML attributes to a Livewire component, you need to explicitly define them as component properties:

```php
// Livewire v3 - requires explicit properties
class Alert extends Component
{
    public string $type = 'info';
    public string $class = '';
    public string $id = '';

    public function render()
    {
        return view('livewire.alert');
    }
}
```

This approach doesn't scale well for components that need to accept many different HTML attributes.

## The Solution

Livewire v4 components should support attribute forwarding just like Blade components:

```php
@php
new class extends Livewire\Component {
    #[Prop]
    public string $type = 'info';
}
@endphp

<div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
    {{ $slot }}
</div>
```

```blade
<!-- Usage -->
<wire:alert type="error" class="mb-4" id="error-alert" data-testid="alert">
    Something went wrong!
</wire:alert>
```

This would make Livewire components much more flexible and allow for better composition with CSS frameworks, testing attributes, and other HTML attributes without requiring explicit property definitions for every possible attribute.


## The implementation

The problem with this kinda thing now is that Livewire components get dehydrated and rehydrated for each request.

Do we simply store the original attributes array as a memo or something so that $attributes can be re-hydrated?

but what if the parent changes the attributes? this is kinda like the slot problem you know?

like if this was even possible, we could make some kind of placeholder attribute that child re-renders with and the parent has full control over? idk...

or we just keep it simple and dissalow attributes to be dynamic from the parent perspective...

or we keep some kinda hash of the attributes and if the attribute hash changes something happens that's more deep?

or we go insanely simple and just render attributes once and never again and don't even hydrate, just like render the same pre-rendered string on subsequent requests? idk... that doesn't seem smart either...

what are your ideas??

## Analysis & Ideas

I think the "keep it simple" approach is probably best. Here's why:

**Option 1: Static attributes (my preference)**
- Attributes are captured on initial render and memoized
- They remain static for the component's lifecycle
- Simple, predictable, and covers 90% of use cases
- Similar to how `wire:key` works - set once, never changes

```php
// Captured once on mount, never changes
<wire:alert type="error" class="mb-4" id="alert-1">
```

**Option 2: Reactive attributes (complex)**
- Track attribute changes from parent
- Re-render child when parent attributes change
- Would need some kind of parent-child communication system
- Probably overkill for most use cases

**Real-world usage patterns:**
Most attributes people want to forward are:
- CSS classes (`class="mb-4 rounded"`)
- Test attributes (`data-testid="alert"`)
- IDs (`id="unique-alert"`)
- ARIA attributes (`aria-label="Close"`)

These are typically static values that don't need to change during the component's lifecycle.

**Implementation approach:**
1. Store attributes in `memo` on initial render
2. Filter out Livewire-specific attributes (`wire:*`)
3. Make `$attributes` available in template like Blade components
4. Keep it simple - no reactivity, just static forwarding

This gives us 90% of the benefit with 10% of the complexity.