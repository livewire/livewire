In real-time applications, it can be helpful to provide a visual indication that the user's device is no longer connected to the internet.

Livewire provides the `wire:offline` directive for such cases.

By adding `wire:offline` to an element inside a Livewire component, it will be hidden by default and become visible when the user loses connection:

```html
<div wire:offline>
    This device is currently offline.    
</div>
```

## Toggling classes

Adding the `class` modifier allows you to add a class to an element when "offline".

```html
<div wire:offline.class="bg-red-300">
```

When the browser goes offline, the element will receive the `bg-red-300` class. The class will be removed again once the user is back online.

You can also perform the inverse, removing classes by adding the `.remove` modifier, similar to how [`wire:loading`](/docs/loading) works.

```html
<div class="bg-green-300" wire:offline.class.remove="bg-green-300">
```

The `bg-green-300` class will be removed from the `<div>` while offline.

## Toggling attributes

Adding the `attr` modifier allows you to add an attribute to an element when offline.

```html
<button wire:offline.attr="disabled">Save</button>
```

Now, when the browser goes offline, the button will be disabled.

