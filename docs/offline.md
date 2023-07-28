In real-time applications, it can be helpful to provide a visual indication that the user's device is no longer connected to the internet.

Livewire provides the `wire:offline` directive for such cases.

By adding `wire:offline` to an element inside a Livewire component, it will be hidden by default and become visible when the user loses connection:

```blade
<div wire:offline>
    This device is currently offline.
</div>
```

## Toggling classes

Adding the `class` modifier allows you to add a class to an element when the user loses their connection. The class will be removed again, once the user is back online:

```blade
<div wire:offline.class="bg-red-300">
```

Or, using the `.remove` modifier, you can remove a class when a user loses their connection. In this example, the `bg-green-300` class will be removed from the `<div>` while the user has lost their connection:

```blade
<div class="bg-green-300" wire:offline.class.remove="bg-green-300">
```

## Toggling attributes

The `.attr` modifier allows you to add an attribute to an element when the user loses their connection. In this example, the "Save" button will be disabled while the user has lost their connection:

```blade
<button wire:offline.attr="disabled">Save</button>
```

