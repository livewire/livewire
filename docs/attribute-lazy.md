The `#[Lazy]` attribute makes a component load only when it becomes visible in the viewport, preventing slow components from blocking the initial page render.

## Basic usage

Apply the `#[Lazy]` attribute to any component that should be lazy-loaded:

```php
<?php // resources/views/components/⚡revenue.blade.php

use Livewire\Attributes\Lazy;
use Livewire\Component;
use App\Models\Transaction;

new #[Lazy] class extends Component { // [tl! highlight]
    public $amount;

    public function mount()
    {
        // Slow database query...
        $this->amount = Transaction::monthToDate()->sum('amount');
    }
};
?>

<div>
    Revenue this month: {{ $amount }}
</div>
```

With `#[Lazy]`, the component initially renders as an empty `<div></div>`, then loads when it enters the viewport—typically when a user scrolls to it.

## Lazy vs Defer

Livewire provides two ways to delay component loading:

* **Lazy loading (`#[Lazy]`)** - Components load when they become visible in the viewport (when the user scrolls to them)
* **Deferred loading (`#[Defer]`)** - Components load immediately after the initial page load is complete

Use lazy loading for components below the fold that users might not scroll to. Use defer for components that are always visible but you want to load after the page renders.

## Rendering placeholders

By default, Livewire renders an empty `<div></div>` before the component loads. You can provide a custom placeholder using the `placeholder()` method:

```php
<?php // resources/views/components/⚡revenue.blade.php

use Livewire\Attributes\Lazy;
use Livewire\Component;
use App\Models\Transaction;

new #[Lazy] class extends Component {
    public $amount;

    public function mount()
    {
        $this->amount = Transaction::monthToDate()->sum('amount');
    }

    public function placeholder() // [tl! highlight:start]
    {
        return <<<'HTML'
        <div>
            <div class="animate-pulse bg-gray-200 h-20 rounded"></div>
        </div>
        HTML;
    } // [tl! highlight:end]
};
?>

<div>
    Revenue this month: {{ $amount }}
</div>
```

Users will see a skeleton placeholder until the component enters the viewport and loads.

> [!warning] Match placeholder element type
> If your placeholder's root element is a `<div>`, your component must also use a `<div>` element.

## Bundling requests

By default, lazy components load in parallel with independent network requests. To bundle multiple lazy components into a single request, use the `bundle` parameter:

```php
<?php // resources/views/components/⚡revenue.blade.php

use Livewire\Attributes\Lazy;
use Livewire\Component;

new #[Lazy(bundle: true)] class extends Component { // [tl! highlight]
    // ...
};
```

Now, if there are ten `revenue` components on the page, all ten will load via a single bundled network request instead of ten parallel requests.

## Alternative approach

### Using the lazy parameter

Instead of the attribute, you can lazy-load specific component instances using the `lazy` parameter:

```blade
<livewire:revenue lazy />
```

This is useful when you only want certain instances of a component to be lazy-loaded.

### Overriding the attribute

If a component has `#[Lazy]` but you want to load it immediately in certain cases, you can override it:

```blade
<livewire:revenue :lazy="false" />
```

## When to use

Use `#[Lazy]` when:

* Components contain slow operations (database queries, API calls) that would delay page load
* The component is below the fold and users might not scroll to it
* You want to improve perceived performance by showing the page faster
* You have multiple expensive components on a single page

## Learn more

For complete documentation on lazy loading, including placeholders, bundling strategies, and passing props, see the [Lazy Loading documentation](/docs/4.x/lazy).

## Reference

```php
#[Lazy(
    bool|null $bundle = null,
)]
```

**`$bundle`** (optional)
- Whether to bundle multiple lazy components into a single network request
- Default: `null` (loads in parallel by default)
