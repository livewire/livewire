# Upgrading from v3 to v4

Livewire v4 introduces several improvements and optimizations while maintaining backward compatibility wherever possible. This guide will help you upgrade from Livewire v3 to v4.

> [!warning] Livewire v4 is currently in beta
> Livewire v4 is still in active development and not yet stable. It's recommended to test thoroughly in a development environment before upgrading production applications. Breaking changes may occur between beta releases.

> [!tip] Smooth upgrade path
> Most applications can upgrade to v4 with minimal changes. The breaking changes are primarily configuration updates and method signature changes that only affect advanced usage.

## Installation

Update your `composer.json` to require Livewire v4 beta:

```bash
composer require livewire/livewire:^4.0@beta
```

After updating, clear your application's cache:

```bash
php artisan config:clear
php artisan view:clear
```

> [!info] View all changes on GitHub
> For a complete overview of all code changes between v3 and v4, you can review the full diff on GitHub: [Compare 3.x to main →](https://github.com/livewire/livewire/compare/3.x...main)

## High-impact changes

These changes are most likely to affect your application and should be reviewed carefully.

### Config file updates

Several configuration keys have been renamed, reorganized, or have new defaults. Update your `config/livewire.php` file:

#### Renamed configuration keys

**Layout configuration:**
```php
// Before (v3)
'layout' => 'components.layouts.app',

// After (v4)
'component_layout' => 'layouts::app',
```

The layout now uses the `layouts::` namespace by default, pointing to `resources/views/layouts/app.blade.php`.

**Placeholder configuration:**
```php
// Before (v3)
'lazy_placeholder' => 'livewire.placeholder',

// After (v4)
'component_placeholder' => 'livewire.placeholder',
```

#### Changed defaults

**Smart wire:key behavior:**
```php
// Now defaults to true (was false in v3)
'smart_wire_keys' => true,
```

This enables automatic intelligent wire:key generation for loops, reducing the need for manual wire:key attributes.

[Learn more about wire:key →](/docs/4.x/nesting#wire-key)

#### New configuration options

**Component locations:**
```php
'component_locations' => [
    resource_path('views/components'),
    resource_path('views/livewire'),
],
```

Defines where Livewire looks for single-file and multi-file (view-based) components.

**Component namespaces:**
```php
'component_namespaces' => [
    'layouts' => resource_path('views/layouts'),
    'pages' => resource_path('views/pages'),
],
```

Creates custom namespaces for organizing view-based components (e.g., `<livewire:pages::dashboard />`).

**Make command defaults:**
```php
'make_command' => [
    'type' => 'sfc',  // Options: 'sfc', 'mfc', or 'class'
    'emoji' => true,   // Whether to use ⚡ emoji prefix
],
```

Configure default component format and emoji usage. Set `type` to `'class'` to match v3 behavior.

**CSP-safe mode:**
```php
'csp_safe' => false,
```

Enable Content Security Policy mode to avoid `unsafe-eval` violations. When enabled, Livewire uses the Alpine CSP build. Note: This mode restricts complex JavaScript expressions in directives like `wire:click="addToCart($event.detail.productId)"` or global references like `window.location`.

### Routing changes

For full-page components, the recommended routing approach has changed:

```php
// Before (v3) - still works but not recommended
Route::get('/dashboard', Dashboard::class);

// After (v4) - recommended for all component types
Route::livewire('/dashboard', Dashboard::class);

// For view-based components, you can use the component name
Route::livewire('/dashboard', 'pages::dashboard');
```

Using `Route::livewire()` is now the preferred method and is required for single-file and multi-file components to work correctly as full-page components.

[Learn more about routing →](/docs/4.x/components#full-page-components)

## Medium-impact changes

These changes may affect certain parts of your application depending on which features you use.

### Performance improvements

Livewire v4 includes significant performance improvements to the request handling system:

- **Non-blocking polling**: `wire:poll` no longer blocks other requests or is blocked by them
- **Parallel live updates**: `wire:model.live` requests now run in parallel, allowing faster typing and quicker results

These improvements happen automatically—no changes needed to your code.

### Method signature changes

If you're extending Livewire's core functionality or using these methods directly, note these signature changes:

**Streaming:**

The `stream()` method parameter order has changed:

```php
// Before (v3)
$this->stream(to: '#container', content: 'Hello', replace: true);

// After (v4)
$this->stream(content: 'Hello', replace: true, el: '#container');
```

If you're using named parameters (as shown above), note that `to:` has been renamed to `el:`. If you're using positional parameters, you'll need to update to the following:

```php
// Before (v3) - positional parameters
$this->stream('#container', 'Hello');

// After (v4) - positional/named parameters
$this->stream('Hello', el: '#container');
```

[Learn more about streaming →](/docs/4.x/wire-stream)

**Component mounting (internal):**

If you're extending `LivewireManager` or calling the `mount()` method directly:

```php
// Before (v3)
public function mount($name, $params = [], $key = null)

// After (v4)
public function mount($name, $params = [], $key = null, $slots = [])
```

This change adds support for passing slots when mounting components and generally won't affect most applications.

## Low-impact changes

These changes only affect applications using advanced features or customization.

### JavaScript deprecations

#### Deprecated: `$wire.$js()` method

The `$wire.$js()` method for defining JavaScript actions has been deprecated:

```js
// Deprecated (v3)
$wire.$js('bookmark', () => {
    // Toggle bookmark...
})

// New (v4)
$wire.$js.bookmark = () => {
    // Toggle bookmark...
}
```

The new syntax is cleaner and more intuitive.

#### Deprecated: `$js` without prefix

The use of `$js` in scripts without `$wire.$js` or `this.$js` prefix has been deprecated:

```js
// Deprecated (v3)
$js('bookmark', () => {
    // Toggle bookmark...
})

// New (v4)
$wire.$js.bookmark = () => {
    // Toggle bookmark...
}
// Or
this.$js.bookmark = () => {
    // Toggle bookmark...
}
```

> [!tip] Old syntax still works
> Both `$wire.$js('bookmark', ...)` and `$js('bookmark', ...)` will continue to work in v4 for backward compatibility, but you should migrate to the new syntax when convenient.

#### Deprecated: `commit` and `request` hooks

The `commit` and `request` hooks have been deprecated in favor of a new interceptor system that provides more granular control and better performance.

> [!tip] Old hooks still work
> The deprecated hooks will continue to work in v4 for backward compatibility, but you should migrate to the new system when convenient.

#### Migrating from `commit` hook

The old `commit` hook:

```js
// OLD - Deprecated
Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
    respond(() => {
        // Runs after response received but before processing
    })

    succeed(({ snapshot, effects }) => {
        // Runs after successful response
    })

    fail(() => {
        // Runs if request failed
    })
})
```

Should be replaced with the new `interceptMessage`:

```js
// NEW - Recommended
Livewire.interceptMessage(({ component, message, onFinish, onSuccess, onError, onFailure }) => {
    onFinish(() => {
        // Equivalent to respond()
    })

    onSuccess(({ payload }) => {
        // Equivalent to succeed()
        // Access snapshot via payload.snapshot
        // Access effects via payload.effects
    })

    onError(() => {
        // Equivalent to fail() for server errors
    })

    onFailure(() => {
        // Equivalent to fail() for network errors
    })
})
```

#### Migrating from `request` hook

The old `request` hook:

```js
// OLD - Deprecated
Livewire.hook('request', ({ url, options, payload, respond, succeed, fail }) => {
    respond(({ status, response }) => {
        // Runs when response received
    })

    succeed(({ status, json }) => {
        // Runs on successful response
    })

    fail(({ status, content, preventDefault }) => {
        // Runs on failed response
    })
})
```

Should be replaced with the new `interceptRequest`:

```js
// NEW - Recommended
Livewire.interceptRequest(({ request, onResponse, onSuccess, onError, onFailure }) => {
    // Access url via request.uri
    // Access options via request.options
    // Access payload via request.payload

    onResponse(({ response }) => {
        // Equivalent to respond()
        // Access status via response.status
    })

    onSuccess(({ response, responseJson }) => {
        // Equivalent to succeed()
        // Access status via response.status
        // Access json via responseJson
    })

    onError(({ response, responseBody, preventDefault }) => {
        // Equivalent to fail() for server errors
        // Access status via response.status
        // Access content via responseBody
    })

    onFailure(({ error }) => {
        // Equivalent to fail() for network errors
    })
})
```

#### Key differences

1. **More granular error handling**: The new system separates network failures (`onFailure`) from server errors (`onError`)
2. **Better lifecycle hooks**: Message interceptors provide additional hooks like `onSync`, `onMorph`, and `onRender`
3. **Cancellation support**: Both messages and requests can be cancelled/aborted
4. **Component scoping**: Interceptors can be scoped to specific components using `Livewire.intercept($wire, ...)`

For complete documentation on the new interceptor system, see the [JavaScript Interceptors documentation](/docs/4.x/javascript#interceptors).

## New features in v4

Livewire v4 introduces several powerful new features you can start using immediately:

### Component features

**Single-file and multi-file components**

v4 introduces new component formats alongside the traditional class-based approach. Single-file components combine PHP and Blade in one file, while multi-file components organize PHP, Blade, JavaScript, and tests in a directory.

By default, view-based component files are prefixed with a ⚡ emoji to distinguish them from regular Blade files in your editor and searches. This can be disabled via the `make_command.emoji` config.

```bash
php artisan make:livewire create-post        # Single-file (default)
php artisan make:livewire create-post --mfc  # Multi-file
php artisan livewire:convert create-post     # Convert between formats
```

[Learn more about component formats →](/docs/4.x/components)

**Slots and attribute forwarding**

Components now support slots and automatic attribute bag forwarding using `{{ $attributes }}`, making component composition more flexible.

[Learn more about nesting components →](/docs/4.x/nesting)

**JavaScript in view-based components**

View-based components can now include `<script>` tags without the `@script` wrapper. These scripts are served as separate cached files for better performance and automatic `$wire` binding:

```blade
<div>
    <!-- Your component template -->
</div>

<script>
    // $wire is automatically bound as 'this'
    this.count++  // Same as $wire.count++

    // $wire is still available if preferred
    $wire.save()
</script>
```

[Learn more about JavaScript in components →](/docs/4.x/javascript)

### Islands

Islands allow you to create isolated regions within a component that update independently, dramatically improving performance without creating separate child components.

```blade
@island(name: 'stats', lazy: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

Islands also support imperative rendering and streaming from your component actions.

[Learn more about islands →](/docs/4.x/islands)

### Loading improvements

**Deferred loading**

In addition to lazy loading (viewport-based), components can now be deferred to load immediately after the initial page load:

```blade
<livewire:revenue defer />
```

```php
#[Defer]
class Revenue extends Component { ... }
```

**Bundled loading**

Control whether multiple lazy/deferred components load in parallel or bundled together:

```blade
<livewire:revenue lazy lazy:bundle />
<livewire:expenses defer defer:bundle />
```

```php
#[Lazy(bundle: true)]
class Revenue extends Component { ... }
```

[Learn more about lazy and deferred loading →](/docs/4.x/lazy)

### Async actions

Run actions in parallel without blocking other requests using the `.async` modifier or `#[Async]` attribute:

```blade
<button wire:click.async="logActivity">Track</button>
```

```php
#[Async]
public function logActivity() { ... }
```

[Learn more about async actions →](/docs/4.x/actions#parallel-execution-with-async)

### New directives and modifiers

**`wire:sort` - Drag-and-drop sorting**

Built-in support for sortable lists with drag-and-drop:

```blade
<ul wire:sort="updateOrder">
    @foreach ($items as $item)
        <li wire:sort:item="{{ $item->id }}">{{ $item->name }}</li>
    @endforeach
</ul>
```

[Learn more about wire:sort →](/docs/4.x/wire-sort)

**`wire:intersect` - Viewport intersection**

Run actions when elements enter or leave the viewport, similar to Alpine's `x-intersect`:

```blade
<!-- Basic usage -->
<div wire:intersect="loadMore">...</div>

<!-- With modifiers -->
<div wire:intersect.once="trackView">...</div>
<div wire:intersect:leave="pauseVideo">...</div>
<div wire:intersect.half="loadMore">...</div>
<div wire:intersect.full="startAnimation">...</div>

<!-- With options -->
<div wire:intersect.margin.200px="loadMore">...</div>
<div wire:intersect.threshold.50="trackScroll">...</div>
```

Available modifiers:
- `.once` - Fire only once
- `.half` - Wait until half is visible
- `.full` - Wait until fully visible
- `.threshold.X` - Custom visibility percentage (0-100)
- `.margin.Xpx` or `.margin.X%` - Intersection margin

[Learn more about wire:intersect →](/docs/4.x/wire-intersect)

**`wire:ref` - Element references**

Easily reference and interact with elements in your template:

```blade
@foreach ($comments as $comment)
    <div wire:ref="comment-{{ $comment->id }}">
        {{ $comment->body }}
    </div>
@endforeach

<button wire:click="$refs['comment-123'].scrollIntoView()">
    Scroll to Comment
</button>
```

[Learn more about wire:ref →](/docs/4.x/wire-ref)

**`.renderless` modifier**

Skip component re-rendering directly from the template:

```blade
<button wire:click.renderless="trackClick">Track</button>
```

This is an alternative to the `#[Renderless]` attribute for actions that don't need to update the UI.

[Learn more about actions →](/docs/4.x/actions)

**`.preserve-scroll` modifier**

Preserve scroll position during updates to prevent layout jumps:

```blade
<button wire:click.preserve-scroll="loadMore">Load More</button>
```

**`data-loading` attribute**

Every element that triggers a network request automatically receives a `data-loading` attribute, making it easy to style loading states with Tailwind:

```blade
<button wire:click="save" class="data-[loading]:opacity-50 data-[loading]:pointer-events-none">
    Save Changes
</button>
```

[Learn more about loading states →](/docs/4.x/wire-loading)

### JavaScript improvements

**`$errors` magic property**

Access your component's error bag from JavaScript:

```blade
<div wire:show="$errors.has('email')">
    <span wire:text="$errors.first('email')"></span>
</div>
```

[Learn more about validation →](/docs/4.x/validation)

**`$intercept` magic**

Intercept and modify Livewire requests from JavaScript:

```blade
<script>
this.$intercept('save', ({ proceed }) => {
    if (confirm('Save changes?')) {
        proceed()
    }
})
</script>
```

[Learn more about JavaScript interceptors →](/docs/4.x/javascript#interceptors)

**Island targeting from JavaScript**

Trigger island renders directly from the template:

```blade
<button wire:click="$refresh" wire:island.prepend="stats">
    Update Stats
</button>
```

[Learn more about islands →](/docs/4.x/islands)

## Getting help

If you encounter issues during the upgrade:

- Check the [documentation](https://livewire.laravel.com) for detailed feature guides
- Visit the [GitHub discussions](https://github.com/livewire/livewire/discussions) for community support
- Report bugs on [GitHub issues](https://github.com/livewire/livewire/issues)
