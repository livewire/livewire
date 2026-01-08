
When a user interacts with your Livewire components, providing visual feedback during network requests is essential for a good user experience. Livewire automatically adds a `data-loading` attribute to any element that triggers a network request, making it easy to style loading states.

> [!tip] Prefer data-loading over wire:loading
> Livewire also provides the [`wire:loading`](/docs/4.x/wire-loading) directive for toggling elements during requests. While `wire:loading` is simpler for basic show/hide scenarios, it has more limitations (requiring `wire:target` for specificity, doesn't work well with events across components, etc.). For most use cases, you should prefer using `data-loading` selectors as demonstrated in this guide.

## Basic usage

Livewire automatically adds a `data-loading` attribute to any element that triggers a network request. This allows you to style loading states directly with CSS or Tailwind without using `wire:loading` directives.

Here's a simple example using a button with `wire:click`:

```blade
<button wire:click="save" class="data-loading:opacity-50">
    Save Changes
</button>
```

When the button is clicked and the request is in-flight, it will automatically become semi-transparent thanks to the `data-loading` attribute being present on the element.

## How it works

The `data-loading` attribute is automatically added to elements that trigger network requests, including:

- Actions: `wire:click="save"`
- Form submissions: `wire:submit="create"`
- Property updates: `wire:model.live="search"`
- Events: `wire:click="$dispatch('refresh')"`

Importantly, the attribute is added even when dispatching events that are handled by other components:

```blade
<button wire:click="$dispatch('refresh-stats')">
    Refresh
</button>
```

Even though the event is picked up by a different component, the button that dispatched the event will still receive the `data-loading` attribute during the network request.

## Styling with Tailwind

Tailwind v4 and above provides powerful selectors for working with the `data-loading` attribute.

### Basic styling

Use Tailwind's `data-loading:` variant to apply styles when an element is loading:

```blade
<button wire:click="save" class="data-loading:opacity-50">
    Save
</button>
```

### Showing elements during loading

To show an element only while loading is active, use the `not-data-loading:hidden` variant:

```blade
<button wire:click="save">
    Save
</button>

<span class="not-data-loading:hidden">
    Saving...
</span>
```

This approach is preferred over `hidden data-loading:block` because it works regardless of the element's display type (flex, inline, grid, etc.).

### Styling children

You can style child elements when a parent has the `data-loading` attribute using the `in-data-loading:` variant:

```blade
<button wire:click="save">
    <span class="in-data-loading:hidden">Save</span>
    <span class="not-in-data-loading:hidden">Saving...</span>
</button>
```

> [!warning] The in-data-loading variant applies to all ancestors
> The `in-data-loading:` variant will trigger if **any** ancestor element (no matter how far up the tree) has the `data-loading` attribute. This can lead to unexpected behavior if you have nested loading states.

### Styling parents

Style parent elements when they contain a child with `data-loading` using the `has-data-loading:` variant:

```blade
<div class="has-data-loading:opacity-50">
    <button wire:click="save">Save</button>
</div>
```

When the button is clicked, the entire parent div will become semi-transparent.

### Styling siblings

You can style sibling elements using Tailwind's `peer` utility with the `peer-data-loading:` variant:

```blade
<div>
    <button wire:click="save" class="peer">
        Save
    </button>

    <span class="peer-data-loading:opacity-50">
        Saving...
    </span>
</div>
```

### Complex selectors

For more advanced styling needs, you can use arbitrary variants to target specific elements:

```blade
<!-- Style all direct children when loading -->
<div class="[&[data-loading]>*]:opacity-50" wire:click="save">
    <span>Child 1</span>
    <span>Child 2</span>
</div>

<!-- Style specific descendant elements -->
<button class="[&[data-loading]_.icon]:animate-spin" wire:click="save">
    <svg class="icon"><!-- spinner --></svg>
    Save
</button>
```

Learn more about Tailwind's state variants and arbitrary selectors in the [Tailwind CSS documentation](https://tailwindcss.com/docs/hover-focus-and-other-states).

## Advantages over wire:loading

The `data-loading` attribute approach offers several advantages over the traditional `wire:loading` directive:

1. **No targeting needed**: Unlike `wire:loading` which often requires `wire:target` to specify which action to respond to, the `data-loading` attribute is automatically scoped to the element that triggered the request.

2. **More elegant styling**: Tailwind's variant system provides a cleaner, more declarative way to style loading states directly in your markup.

3. **Works with events**: The attribute is added even when dispatching events that are handled by other components, something that was previously difficult to achieve with `wire:loading`.

4. **Better composition**: Styling with Tailwind variants composes better with other utility classes and states.

## Tailwind 4 requirement

> [!info] Tailwind v4+ required for advanced variants
> The `in-data-loading:`, `has-data-loading:`, `peer-data-loading:`, and `not-data-loading:` variants require Tailwind CSS v4 or above. If you're using an earlier version of Tailwind, you can still target the `data-loading` attribute using the `data-loading:` syntax or standard CSS.

## Using with plain CSS

If you're not using Tailwind, you can target the `data-loading` attribute with standard CSS:

```css
[data-loading] {
    opacity: 0.5;
}

button[data-loading] {
    background-color: #ccc;
}
```

You can also use CSS to style child elements:

```css
[data-loading] .loading-text {
    display: inline;
}

[data-loading] .default-text {
    display: none;
}
```

## See also

- **[wire:loading](/docs/4.x/wire-loading)** — Show and hide elements during requests
- **[Actions](/docs/4.x/actions)** — Display feedback during action processing
- **[Forms](/docs/4.x/forms)** — Indicate form submission progress
- **[Lazy Loading](/docs/4.x/lazy)** — Show loading states for lazy components
