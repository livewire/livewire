Livewire allows you to _teleport_ part of your template to another part of the DOM on the page entirely.

This is useful for things like nested dialogs. When nesting one dialog inside of another, the z-index of the parent modal is applied to the nested modal. This can cause problems with styling backdrops and overlays. To avoid this problem, you can use Livewire's `@teleport` directive to render each nested modal as siblings in the rendered DOM.

This functionality is powered by [Alpine's `x-teleport` directive](https://alpinejs.dev/directives/teleport).

## Basic usage

To _teleport_ a portion of your template to another part of the DOM, you can wrap it in Livewire's `@teleport` directive.

Below is an example of using `@teleport` to render a modal dialog's contents at the end of the `<body>` element on the page:

```blade
<div>
    <!-- Modal -->
    <div x-data="{ open: false }">
        <button @click="open = ! open">Toggle Modal</button>

        @teleport('body')
            <div x-show="open">
                Modal contents...
            </div>
        @endteleport
    </div>
</div>
```

> [!info]
> The `@teleport` selector can be any string you would normally pass into something like `document.querySelector()`.
>
> You can learn more about `document.querySelector()` by consulting its [MDN documentation](https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelector).

Now, when the above Livewire template is rendered on the page, the _contents_ portion of the modal will be rendered at the end of `<body>`:

```html
<body>
    <!-- ... -->

    <div x-show="open">
        Modal contents...
    </div>
</body>
```

> [!warning] You must teleport outside the component
> Livewire only supports teleporting HTML outside your components. For example, teleporting a modal to the `<body>` tag is fine, but teleporting it to another element within your component will not work.

> [!warning] Teleporting only works with a single root element
> Make sure you only include a single root element inside your `@teleport` statement.
