Livewire allows you to lazy load components that would otherwise slow down the initial page load.

## Lazy vs Defer

Livewire provides two ways to delay component loading:

- **Lazy loading (`lazy`)**: Components load when they become visible in the viewport (when the user scrolls to them)
- **Deferred loading (`defer`)**: Components load immediately after the initial page load is complete

Both approaches prevent slow components from blocking your initial page render, but differ in when the component actually loads.

## Basic example

For example, imagine you have a `revenue` component which contains a slow database query in `mount()`:

```php
<?php // resources/views/components/⚡revenue.blade.php

use Livewire\Component;
use App\Models\Transaction;

new class extends Component
{
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

Without lazy loading, this component would delay the loading of the entire page and make your entire application feel slow.

To enable lazy loading, you can pass the `lazy` parameter into the component:

```blade
<livewire:revenue lazy />
```

Now, instead of loading the component right away, Livewire will skip this component, loading the page without it. Then, when the component is visible in the viewport, Livewire will make a network request to fully load this component on the page.

> [!info] Lazy and deferred requests are isolated by default
> Unlike other network requests in Livewire, lazy and deferred component updates are isolated from each other when sent to the server. This keeps loading fast by loading each component in parallel. [Read more about bundling components →](#bundling-multiple-lazy-components)

## Rendering placeholder HTML

By default, Livewire will insert an empty `<div></div>` for your component before it is fully loaded. As the component will initially be invisible to users, it can be jarring when the component suddenly appears on the page.

To signal to your users that the component is being loaded, you can define a `placeholder()` method to render any kind of placeholder HTML you like, including loading spinners and skeleton placeholders:

```php
<?php // resources/views/components/⚡revenue.blade.php

use Livewire\Component;
use App\Models\Transaction;

new class extends Component
{
    public $amount;

    public function mount()
    {
        // Slow database query...
        $this->amount = Transaction::monthToDate()->sum('amount');
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            <!-- Loading spinner... -->
            <svg>...</svg>
        </div>
        HTML;
    }
};
?>

<div>
    Revenue this month: {{ $amount }}
</div>
```

Because the above component specifies a "placeholder" by returning HTML from a `placeholder()` method, the user will see an SVG loading spinner on the page until the component is fully loaded.

> [!warning] The placeholder and the component must share the same element type
> For instance, if your placeholder's root element type is a 'div,' your component must also use a 'div' element.

### Rendering a placeholder via a view

For more complex loaders (such as skeletons) you can return a `view` from the `placeholder()` similar to `render()`.

```php
public function placeholder(array $params = [])
{
    return view('livewire.placeholders.skeleton', $params);
}
```

Any parameters from the component being lazy loaded will be available as an `$params` argument passed to the `placeholder()` method.

## Loading immediately after page load

By default, lazy-loaded components aren't fully loaded until they enter the browser's viewport, for example when a user scrolls to one.

If you'd rather load components immediately after the page is loaded, without waiting for them to enter the viewport, you can use the `defer` parameter instead:

```blade
<livewire:revenue defer />
```

Now this component will load as soon as the page is ready without waiting for it to be visible in the viewport.

You can also use the `#[Defer]` attribute to make a component defer-loaded by default:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Defer;

#[Defer]
class Revenue extends Component
{
    // ...
}
```

> [!tip] Legacy on-load syntax
> You can also use `lazy="on-load"` which behaves the same as `defer`. The `defer` parameter is recommended for new code.

## Passing in props

In general, you can treat `lazy` components the same as normal components, since you can still pass data into them from outside.

For example, here's a scenario where you might pass a time interval into the `Revenue` component from a parent component:

```blade
<input type="date" wire:model="start">
<input type="date" wire:model="end">

<livewire:revenue lazy :$start :$end />
```

You can accept this data in `mount()` just like any other component:

```php
<?php // resources/views/components/⚡revenue.blade.php

use Livewire\Component;
use App\Models\Transaction;

new class extends Component
{
    public $amount;

    public function mount($start, $end)
    {
        // Expensive database query...
        $this->amount = Transactions::between($start, $end)->sum('amount');
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            <!-- Loading spinner... -->
            <svg>...</svg>
        </div>
        HTML;
    }
};
?>

<div>
    Revenue this month: {{ $amount }}
</div>
```

However, unlike a normal component load, a `lazy` component has to serialize or "dehydrate" any passed-in properties and temporarily store them on the client-side until the component is fully loaded.

For example, you might want to pass in an Eloquent model to the `revenue` component like so:

```blade
<livewire:revenue lazy :$user />
```

In a normal component, the actual PHP in-memory `$user` model would be passed into the `mount()` method of `revenue`. However, because we won't run `mount()` until the next network request, Livewire will internally serialize `$user` to JSON and then re-query it from the database before the next request is handled.

Typically, this serialization should not cause any behavioral differences in your application.

## Enforcing lazy or defer by default

If you want to enforce that all usages of a component will be lazy-loaded or deferred, you can add the `#[Lazy]` or `#[Defer]` attribute above the component class:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class Revenue extends Component
{
    // ...
}
```

Or for deferred loading:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Defer;

#[Defer]
class Revenue extends Component
{
    // ...
}
```

You can override these defaults when rendering a component:

```blade
{{-- Disable lazy loading --}}
<livewire:revenue :lazy="false" />

{{-- Disable deferred loading --}}
<livewire:revenue :defer="false" />
```

## Bundling multiple lazy components

By default, if there are multiple lazy-loaded components on the page, each component will make an independent network request in parallel. This is often desirable for performance as each component loads independently.

However, if you have many lazy components on a page, you may want to bundle them into a single network request to reduce server overhead.

### Using the bundle parameter

You can enable bundling using the `bundle: true` parameter:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy(bundle: true)]
class Revenue extends Component
{
    // ...
}
```

Now, if there are ten `Revenue` components on the same page, when the page loads, all ten updates will be bundled and sent to the server as a single network request.

### Using the bundle modifier

You can also enable bundling inline when rendering a component using the bundle modifier:

```blade
<livewire:revenue lazy.bundle />
```

This also works with deferred components:

```blade
<livewire:revenue defer.bundle />
```

Or using the attribute:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Defer;

#[Defer(bundle: true)]
class Revenue extends Component
{
    // ...
}
```

### When to use bundling

**Use bundling when:**
- You have many (5+) lazy or deferred components on a single page
- The components are similar in complexity and load time
- You want to reduce server overhead and HTTP connection count

**Don't use bundling when:**
- Components have vastly different load times (slow components will block fast ones)
- You want components to appear as soon as they're individually ready
- You only have a few lazy components on the page

> [!tip] Legacy isolate syntax
> You can also use `isolate: false` which behaves the same as `bundle: true`. The `bundle` parameter is recommended for new code as it's more explicit about the intent.

## Full-page lazy loading

You can lazy load or defer full-page Livewire components using route methods.

### Lazy loading full pages

Use `->lazy()` to load the component when it enters the viewport:

```php
Route::livewire('/dashboard', 'pages::dashboard')->lazy();
```

### Deferring full pages

Use `->defer()` to load the component immediately after the page loads:

```php
Route::livewire('/dashboard', 'pages::dashboard')->defer();
```

### Disabling lazy/defer loading

If a component is lazy or deferred by default (via the `#[Lazy]` or `#[Defer]` attribute), you can opt-out using `enabled: false`:

```php
Route::livewire('/dashboard', 'pages::dashboard')->lazy(enabled: false);
Route::livewire('/dashboard', 'pages::dashboard')->defer(enabled: false);
```

## Default placeholder view

If you want to set a default placeholder view for all your components you can do so by referencing the view in the `/config/livewire.php` config file:

```php
'component_placeholder' => 'livewire.placeholder',
```

Now, when a component is lazy-loaded and no `placeholder()` is defined, Livewire will use the configured Blade view (`livewire.placeholder` in this case.)

## Disabling lazy loading for tests

When unit testing a lazy component, or a page with nested lazy components, you may want to disable the "lazy" behavior so that you can assert the final rendered behavior. Otherwise, those components would be rendered as their placeholders during your tests.

You can easily disable lazy loading using the `Livewire::withoutLazyLoading()` testing helper like so:

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Dashboard;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::withoutLazyLoading() // [tl! highlight]
            ->test(Dashboard::class)
            ->assertSee(...);
    }
}
```

Now, when the dashboard component is rendered for this test, it will skip rendering the `placeholder()` and instead render the full component as if lazy loading wasn't applied at all.
