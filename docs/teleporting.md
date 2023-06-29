Livewire allows you to "teleport" part of your Blade template to another part of the DOM on the page entirely.

This is useful for things like modals (especially nesting them), where it's helpful to break out of the z-index of the current Livewire component.

> This functionality is powered by [Alpine.js' `x-teleport` directive](https://alpinejs.dev/directives/teleport) and is a thin abstraction on top of that API.

## Basic usage

By wrapping an element in `@teleport`, you are telling Live to "append" that element to the provided selector.

> The `@teleport` selector can be any string you would normally pass into something like `document.querySelector`. It will find the first element that matches, be it a tag name (`body`), class name (`.my-class`), ID (`#my-id`), or any other valid CSS selector.

[→ Read more about `document.querySelector`](https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelector)

Here's a contrived modal example:

```blade
<body>
    <div x-data="{ open: false }">
        <button @click="open = ! open">Toggle Modal</button>

        @teleport('body')
            <div x-show="open">
                Modal contents...
            </div>
        @endteleport
    </div>

    <div>Some other content placed AFTER the modal markup.</div>

    ...

</body>
```
