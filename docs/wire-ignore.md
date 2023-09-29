Fortunately a library like Pikaday adds its extra DOM at the end of the page. Many other libraries manipulate the DOM as soon as they are initialized and continue to mutate the DOM as you interact with them.

When this happens, it's hard for Livewire to keep track of what DOM manipulations you want to preserve on component updates, and which you want to discard.

To tell Livewire to ignore changes to a subset of HTML within your component, you can add the wire:ignore directive.

The Select2 library is one of those libraries that takes over its portion of the DOM (it replaces your <select> tag with lots of custom markup).

Here is an example of using the Select2 library inside a Livewire component to demonstrate the usage of wire:ignore.


```blade
<div wire:ignore>
</div>
```

```blade
<div wire:ignore.self>
</div>
```
