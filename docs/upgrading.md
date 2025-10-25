# Upgrading from v3 to v4

Livewire v4 introduces several improvements and optimizations while maintaining backward compatibility wherever possible. This guide will help you upgrade from Livewire v3 to v4.

> [!tip] Smooth upgrade path
> Most applications can upgrade to v4 with minimal changes. The breaking changes are primarily configuration updates and method signature changes that only affect advanced usage.

## Installation

Update your `composer.json` to require Livewire v4:

```bash
composer require livewire/livewire:^4.0
```

After updating, clear your application's cache:

```bash
php artisan config:clear
php artisan view:clear
```

## High-impact changes

These changes are most likely to affect your application and should be reviewed carefully.

### Config file updates

Several configuration keys have been renamed or reorganized. Update your `config/livewire.php` file:

**Layout configuration:**
```php
// Before (v3)
'layout' => 'components.layouts.app',

// After (v4)
'component_layout' => 'layouts::app',
```

**Lazy placeholder configuration:**
```php
// Before (v3)
'lazy_placeholder' => 'livewire.placeholder',

// After (v4)
'component_placeholder' => 'livewire.placeholder',
```

**New configuration options:**

```php
// Define custom component locations
'component_locations' => [
    resource_path('views/admin/components'),
],

// Define custom component namespaces
'component_namespaces' => [
    'admin' => resource_path('views/admin/components'),
],

// Configure make command defaults
'make_command' => [
    'type' => 'sfc',  // 'sfc', 'mfc', or 'class'
    'emoji' => true,  // Whether to use emoji prefix (⚡)
],
```

## Medium-impact changes

These changes may affect certain parts of your application depending on which features you use.

### Method signature changes

If you're extending Livewire's core functionality, these method signatures have changed:

**Component mounting:**
```php
// Before (v3)
mount($name, $params = [], $key = null)

// After (v4)
mount($name, $params = [], $key = null, $slots = [])
```

**Streaming:**
```php
// Before (v3)
stream($name, $content, $replace = false)

// After (v4)
stream($content, $replace = false, $name = null)
```

The parameter order has changed to make `$name` optional, as it's typically not needed.

## Low-impact changes

These changes only affect applications using advanced JavaScript customization.

### JavaScript hooks: New interceptor system

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

For complete documentation on the new interceptor system, see the [JavaScript Interceptors documentation](/docs/javascript#interceptors).

## New features in v4

Livewire v4 introduces several powerful new features you can start using immediately:

### Single-file and multi-file components

v4 introduces new component formats alongside the traditional class-based approach:

- **Single-file components (SFC)**: Combine PHP and Blade in one file
- **Multi-file components (MFC)**: Separate PHP, Blade, JavaScript, and tests into a directory

```bash
# Create a single-file component (default)
php artisan make:livewire create-post

# Create a multi-file component
php artisan make:livewire create-post --mfc

# Convert between formats
php artisan livewire:convert create-post
```

[Learn more about component formats →](/docs/components)

### Islands

Islands allow you to create isolated regions within a component that update independently, improving performance without creating separate child components:

```blade
@island(name: 'stats', lazy: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

[Learn more about islands →](/docs/islands)

### Deferred loading

In addition to lazy loading (viewport-based), you can now defer components to load immediately after the page:

```blade
<livewire:revenue defer />
```

```php
#[Defer]
class Revenue extends Component { ... }
```

[Learn more about lazy and deferred loading →](/docs/lazy)

### Bundled lazy loading

Control whether multiple lazy/deferred components load in parallel or bundled:

```php
#[Lazy(bundle: true)]
class Revenue extends Component { ... }
```

```blade
<livewire:revenue lazy:bundle />
```

### Async actions

Run actions in parallel without blocking other requests:

```blade
<button wire:click.async="logActivity">Track</button>
```

```php
#[Async]
public function logActivity() { ... }
```

[Learn more about async actions →](/docs/actions#parallel-execution-with-async)

### Drag-and-drop sorting

Built-in support for sortable lists:

```blade
<ul wire:sort="updateOrder">
    @foreach ($items as $item)
        <li wire:sort:item="{{ $item->id }}">{{ $item->name }}</li>
    @endforeach
</ul>
```

[Learn more about wire:sort →](/docs/wire-sort)

## Getting help

If you encounter issues during the upgrade:

- Check the [documentation](https://livewire.laravel.com) for detailed feature guides
- Visit the [GitHub discussions](https://github.com/livewire/livewire/discussions) for community support
- Report bugs on [GitHub issues](https://github.com/livewire/livewire/issues)
