Livewire allows you to include component-specific styles directly in your single-file and multi-file components. These styles are automatically scoped to your component, preventing them from leaking into other parts of your application.

This approach mirrors how `<script>` tags work in Livewire components, giving you a cohesive way to keep your component's PHP, HTML, JavaScript, and CSS all in one place.

## Scoped styles

By default, styles defined in your component are scoped to that component only. This means your CSS selectors will only affect elements within your component, even if those same selectors exist elsewhere on the page.

### Single-file components

Add a `<style>` tag at the root level of your single-file component:

```blade
<?php

use Livewire\Component;

new class extends Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
};
?>

<div>
    <h1 class="title">Count: {{ $count }}</h1>
    <button class="btn" wire:click="increment">+</button>
</div>

<style>
.title {
    color: blue;
    font-size: 2rem;
}

.btn {
    background: indigo;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
}
</style>
```

The `.title` and `.btn` styles will only apply to elements within this component, not to any other elements on the page with the same classes.

### Multi-file components

For multi-file components, create a CSS file with the same name as your component:

```
resources/views/components/counter/
├── counter.php
├── counter.blade.php
└── counter.css          # Scoped styles
```

`counter.css`
```css
.title {
    color: blue;
    font-size: 2rem;
}

.btn {
    background: indigo;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
}
```

## How scoping works

Livewire automatically wraps your styles in a selector that targets your component's root element. Behind the scenes, your CSS is transformed using CSS nesting:

```css
/* What you write */
.btn { background: blue; }

/* What gets served */
[wire\:name="counter"] {
    .btn { background: blue; }
}
```

This uses the `wire:name` attribute that Livewire adds to every component's root element, ensuring styles only apply within that component.

### Targeting the component root

You can use the `&` selector to target the component's root element itself:

```blade
<style>
& {
    border: 2px solid gray;
    padding: 1rem;
}

.title {
    margin-top: 0;
}
</style>
```

This will add a border and padding to the component's outermost element.

## Global styles

Sometimes you need styles that apply globally rather than being scoped to a single component. Add the `global` attribute to your style tag:

### Single-file components

```blade
<style global>
body {
    font-family: system-ui, sans-serif;
}

.prose {
    max-width: 65ch;
    line-height: 1.6;
}
</style>
```

### Multi-file components

Create a file with `.global.css` extension:

```
resources/views/components/counter/
├── counter.php
├── counter.blade.php
├── counter.css           # Scoped styles
└── counter.global.css    # Global styles
```

## Combining scoped and global styles

You can use both scoped and global styles in the same component:

```blade
<?php

use Livewire\Component;

new class extends Component {
    // ...
};
?>

<div class="counter">
    <h1 class="title">My Counter</h1>
</div>

<style>
.title {
    color: blue;
}
</style>

<style global>
.counter-page-layout {
    display: grid;
    place-items: center;
}
</style>
```

## Style deduplication

Livewire automatically deduplicates styles when multiple instances of the same component appear on a page. The styles are only loaded once, regardless of how many component instances exist.

## When to use component styles

**Use scoped styles when:**
- Styling is specific to a single component
- You want to avoid CSS class name collisions
- You're building reusable, self-contained components

**Use global styles when:**
- You need to style elements outside your component
- You're defining utility classes used across multiple components
- You're overriding third-party library styles

**Use `@assets` for external stylesheets:**
- When loading CSS from a CDN
- When including third-party library styles

```blade
@assets
<link rel="stylesheet" href="https://cdn.example.com/library.css">
@endassets
```

## Browser support

Scoped styles use [CSS nesting](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_nesting), which is supported in all modern browsers (Chrome 120+, Firefox 117+, Safari 17.2+). For older browser support, consider using a CSS preprocessor or the `@assets` directive with pre-compiled stylesheets.

## See also

- **[JavaScript](/docs/4.x/javascript)** - Using JavaScript in components
- **[Components](/docs/4.x/components)** - Component formats and organization
- **[Alpine](/docs/4.x/alpine)** - Client-side interactivity with Alpine.js
