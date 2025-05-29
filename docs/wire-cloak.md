
`wire:cloak` is a directive that hides elements on page load until Livewire is fully initialized. This is useful for preventing the "flash of unstyled content" that can occur when the page loads before Livewire has a chance to initialize.

## Basic usage

To use `wire:cloak`, add the directive to any element you want to hide during page load:

```blade
<div wire:cloak>
    This content will be hidden until Livewire is fully loaded
</div>
```

### Dynamic content

`wire:cloak` is particularly useful in scenarios where you want to prevent users from seeing uninitialized dynamic content such as element shown or hidden using `wire:show`.

```blade
<div>
    <div wire:show="starred" wire:cloak>
        <!-- Yellow star icon... -->
    </div>

    <div wire:show="!starred" wire:cloak>
        <!-- Gray star icon... -->
    </div>
</div>
```

In the above example, without `wire:cloak`, both icons would be shown before Livewire initializes. However, with `wire:cloak`, both elements will be hidden until initialization.
