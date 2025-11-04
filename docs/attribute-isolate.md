The `#[Isolate]` attribute prevents a component's requests from being bundled with other component updates, allowing it to execute in parallel.

## Basic usage

Apply the `#[Isolate]` attribute to any component that should send isolated requests:

```php
<?php // resources/views/components/post/⚡show.blade.php

use Livewire\Attributes\Isolate;
use Livewire\Component;
use App\Models\Post;

#[Isolate] // [tl! highlight]
new class extends Component
{
    public Post $post;

    public function refreshStats()
    {
        // Expensive operation...
        $this->post->recalculateStatistics();
    }
};
```

With `#[Isolate]`, this component's requests will no longer be bundled with other component updates, allowing them to execute in parallel.

## How request bundling works

By default, when multiple components trigger updates at the same time, Livewire bundles them into a single network request. This reduces server load and enables features like reactive properties and modelable bindings.

However, if a component performs expensive operations, bundling can slow down the entire request. Isolating that component allows it to run in parallel with other updates.

## When to use

Use `#[Isolate]` when:

* The component performs expensive operations (complex queries, API calls, heavy computations)
* Multiple components use `wire:poll` and you want independent polling intervals
* Components listen for events and you don't want one slow component to block others
* The component doesn't need to coordinate with other components on the page

## Example: Polling components

Here's a practical example with multiple polling components:

```php
<?php // resources/views/components/⚡system-status.blade.php

use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate] // [tl! highlight]
new class extends Component
{
    public function checkStatus()
    {
        // Expensive external API call...
        return ExternalService::getStatus();
    }
};
```

```blade
<div wire:poll.5s>
    Status: {{ $this->checkStatus() }}
</div>
```

Without `#[Isolate]`, this component's slow API call would delay other components on the page. With it, the component polls independently without blocking others.

## Lazy components are isolated by default

When using the `#[Lazy]` attribute, components are automatically isolated to load in parallel. You can disable this behavior if needed:

```php
<?php // resources/views/components/⚡revenue.blade.php

use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy(isolate: false)] // [tl! highlight]
new class extends Component
{
    // ...
};
```

Now multiple `revenue` components will bundle their lazy-load requests into a single network request.

## Trade-offs

**Benefits:**
* Prevents slow components from blocking other updates
* Allows true parallel execution of expensive operations
* Independent polling and event handling

**Drawbacks:**
* More network requests to the server
* Can't coordinate with other components in the same request
* Slightly higher server overhead from multiple connections

## Learn more

For more information about request bundling and performance optimization, see the [Request Bundling documentation](/docs/4.x/bundling).
