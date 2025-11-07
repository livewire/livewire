The `@teleport` directive renders a portion of your template in a different location in the DOM, outside the component's normal placement.

## Basic usage

Wrap content with `@teleport` and specify where to render it using a CSS selector:

```blade
<div>
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

The modal content will be rendered at the end of the `<body>` element:

```html
<body>
    <!-- Page content... -->

    <div x-show="open">
        Modal contents...
    </div>
</body>
```

> [!info] Any valid CSS selector
> The `@@teleport` selector can be any string you would pass to `document.querySelector()`, such as `'body'`, `'#modal-root'`, or `'.modal-container'`.

## Why use teleport?

Teleporting is useful for nested modals, dropdowns, and popovers where parent styles or z-index values can interfere with proper rendering.

**Without teleporting:**
```blade
<div style="z-index: 10;">
    <!-- Parent modal with z-index: 10 -->

    <div style="z-index: 20;">
        <!-- Child modal inherits parent's stacking context -->
        <!-- Backdrop may not cover parent modal properly -->
    </div>
</div>
```

**With teleporting:**
```blade
<div style="z-index: 10;">
    <!-- Parent modal -->

    @teleport('body')
        <div style="z-index: 20;">
            <!-- Child modal rendered as sibling at body level -->
            <!-- Backdrop can cover everything properly -->
        </div>
    @endteleport
</div>
```

## Common use cases

**Modal dialogs:**
```blade
@teleport('body')
    <div class="fixed inset-0 bg-black/50" x-show="showModal">
        <div class="modal">
            <!-- Modal content... -->
        </div>
    </div>
@endteleport
```

**Dropdown menus:**
```blade
@teleport('body')
    <div class="absolute" x-show="open" style="top: {{ $top }}px; left: {{ $left }}px;">
        <!-- Dropdown items... -->
    </div>
@endteleport
```

**Toast notifications:**
```blade
@teleport('#notifications-container')
    <div class="toast">
        {{ $message }}
    </div>
@endteleport
```

## Important constraints

> [!warning] Must teleport outside the component
> Livewire only supports teleporting HTML outside your components. Teleporting to another element within the same component will not work.

> [!warning] Single root element required
> Only include a single root element inside your `@@teleport` statement. Multiple root elements are not supported.

**Valid:**
```blade
@teleport('body')
    <div>
        <h2>Title</h2>
        <p>Content</p>
    </div>
@endteleport
```

**Invalid:**
```blade
@teleport('body')
    <h2>Title</h2>
    <p>Content</p>
@endteleport
```

## Powered by Alpine

This functionality uses [Alpine's `x-teleport` directive](https://alpinejs.dev/directives/teleport) under the hood.

[Learn more about teleporting content →](/docs/4.x/teleport)
