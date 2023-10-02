
Livewire offers a `wire:init` directive to run an action as soon as the component is rendered. This can be helpful in cases where you don't want to hold up the entire page load, but want to load some data immediately after the page load.

```blade
<div wire:init="loadPosts">
    <!-- ... -->
</div>
```

The `loadPosts` action will be run immediately after the Livewire component renders on the page.

In most cases however, [Livewire's lazy loading feature](/docs/lazy) is preferable to using `wire:init`.
