Islands allow you to create isolated regions within a Livewire component that update independently. When an action occurs inside an island, only that island re-renders — not the entire component.

This gives you the performance benefits of breaking components into smaller pieces without the overhead of creating separate child components, managing props, or dealing with component communication.

## Basic usage

To create an island, wrap any portion of your Blade template with the `@island` directive:

```blade
<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Revenue;

new class extends Component
{
    #[Computed]
    public function revenue()
    {
        // Expensive calculation or query...
        return Revenue::yearToDate();
    }
};
?>

<div>
    @island
        <div>
            Revenue: {{ $this->revenue }}

            <button type="button" wire:click="$refresh">Refresh</button>
        </div>
    @endisland

    <div>
        <!-- Other content... -->
    </div>
</div>
```

When the "Refresh" button is clicked, only the island containing the revenue calculation will re-render. The rest of the component remains untouched.

Because the expensive calculation is inside a computed property—which evaluates on-demand—it will only be called when the island re-renders, not when other parts of the page update. However, since islands load with the page by default, the `revenue` property will still be calculated during the initial page load.

## Lazy loading

Sometimes you have expensive computations or slow API calls that shouldn't block your initial page load. You can defer an island's initial render until after the page loads using the `lazy` parameter:

```blade
<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Revenue;

new class extends Component
{
    #[Computed]
    public function revenue()
    {
        // Expensive calculation or query...
        return Revenue::yearToDate();
    }
};
?>

<div>
    @island(lazy: true)
        <div>
            Revenue: {{ $this->revenue }}

            <button type="button" wire:click="$refresh">Refresh</button>
        </div>
    @endisland

    <div>
        <!-- Other content... -->
    </div>
</div>
```

The island will display a loading state initially, then fetch and render its content in a separate request.

### Lazy vs Deferred loading

By default, `lazy` uses an intersection observer to trigger the load when the island becomes visible in the viewport. If you want the island to load immediately after the page loads (regardless of visibility), use `defer` instead:

```blade
{{-- Loads when scrolled into view --}}
@island(lazy: true)
    <!-- ... -->
@endisland

{{-- Loads immediately after page load --}}
@island(defer: true)
    <!-- ... -->
@endisland
```

### Custom loading states

You can customize what displays while a lazy island is loading using the `@placeholder` directive:

```blade
@island(lazy: true)
    @placeholder
        <!-- Loading indicator -->
        <div class="animate-pulse">
            <div class="h-32 bg-gray-200 rounded"></div>
        </div>
    @endplaceholder

    <div>
        Revenue: {{ $this->revenue }}

        <button type="button" wire:click="$refresh">Refresh</button>
    </div>
@endisland
```

## Named islands

To trigger an island from elsewhere in your component, give it a name and reference it using `wire:island`:

```blade
<div>
    @island(name: 'revenue')
        <div>
            Revenue: {{ $this->revenue }}
        </div>
    @endisland

    <button type="button" wire:click="$refresh" wire:island="revenue">
        Refresh revenue
    </button>
</div>
```

The `wire:island` directive works alongside action directives like `wire:click`, `wire:submit`, etc. to scope their updates to a specific island.

When you have multiple islands with the same name, they're linked together and will always render as a group:

```blade
@island(name: 'revenue')
    <div class="sidebar">
        Revenue: {{ $this->revenue }}
    </div>
@endisland

@island(name: 'revenue')
    <div class="header">
        Revenue: {{ $this->revenue }}
    </div>
@endisland

<button type="button" wire:click="$refresh" wire:island="revenue">
    Refresh revenue
</button>
```

Both islands will update together whenever one is triggered.

## Append and prepend modes

Instead of replacing content entirely, islands can append or prepend new content. This is perfect for pagination, infinite scroll, or real-time feeds:

```blade
<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Activity;

new class extends Component
{
    public $page = 1;

    public function loadMore()
    {
        $this->page++;
    }

    #[Computed]
    public function activities()
    {
        return Activity::latest()
            ->forPage($this->page, 10)
            ->get();
    }
};
?>

<div>
    @island(name: 'feed')
        @foreach ($this->activities as $activity)
            <x-activity-item :activity="$activity" />
        @endforeach
    @endisland

    <button type="button" wire:click="loadMore" wire:island.append="feed">
        Load more
    </button>
</div>
```

Available modes:
- `wire:island` - Replace/morph content (default)
- `wire:island.append` - Add to the end
- `wire:island.prepend` - Add to the beginning

## Nested islands

Islands can be nested inside each other. When an outer island re-renders, inner islands are skipped by default:

```blade
@island(name: 'revenue')
    <div>
        Total revenue: {{ $this->revenue }}

        @island(name: 'breakdown')
            <div>
                Monthly breakdown: {{ $this->monthlyBreakdown }}

                <button type="button" wire:click="$refresh">
                    Refresh breakdown
                </button>
            </div>
        @endisland

        <button type="button" wire:click="$refresh">
            Refresh revenue
        </button>
    </div>
@endisland
```

Clicking "Refresh revenue" updates only the outer island, while "Refresh breakdown" updates only the inner island.

## Always render with parent

By default, when a component re-renders, islands are skipped. Use the `always` parameter to force an island to update whenever the parent component updates:

```blade
<div>
    @island(always: true)
        <div>
            Revenue: {{ $this->revenue }}

            <button type="button" wire:click="$refresh">Refresh revenue</button>
        </div>
    @endisland

    <button type="button" wire:click="$refresh">Refresh</button>
</div>
```

With `always: true`, the island will re-render whenever any part of the component updates. This is useful for critical data that should always stay in sync with the component state.

This also works for nested islands — an inner island with `always: true` will update whenever its parent island updates.

## Skip initial render

The `skip` parameter prevents an island from rendering initially, perfect for on-demand content:

```blade
@island(skip: true)
    @placeholder
        <button type="button" wire:click="$refresh">
            Load revenue details
        </button>
    @endplaceholder

    <div>
        Revenue: {{ $this->revenue }}

        <button type="button" wire:click="$refresh">Refresh</button>
    </div>
@endisland
```

The placeholder content will be shown initially. When triggered, the island renders and replaces the placeholder.

## Island polling

You can use `wire:poll` within an island to refresh just that island on an interval:

```blade
@island(skip: true)
    <div wire:poll.3s>
        Revenue: {{ $this->revenue }}

        <button type="button" wire:click="$refresh">Refresh</button>
    </div>
@endisland
```

The polling is scoped to the island — only the island will refresh every 3 seconds, not the entire component.

## Rendering islands imperatively

In addition to using `wire:island` to trigger island updates, you can programmatically render islands from within your component actions using the `renderIsland()` method.

### Basic imperative rendering

The `renderIsland()` method renders an island and includes it in the response alongside the normal component render:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;

        // Re-render the 'counter' island in addition to the full component
        $this->renderIsland('counter');
    }
};
?>
```

```blade
<div>
    @island(name: 'counter')
        <div>Count: {{ $count }}</div>
    @endisland

    <button wire:click="increment">Increment</button>
</div>
```

When the "Increment" button is clicked, both the full component and the 'counter' island will render and be sent to the frontend.

### Skipping the component render

Often when rendering an island imperatively, you don't need to re-render the entire component. Use `skipRender()` to only send the island update:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;

        // Only render the island, not the whole component
        $this->renderIsland('counter');
        $this->skipRender();
    }
};
?>
```

Now only the island will update, improving performance by avoiding unnecessary rendering.

### Render modes

The `renderIsland()` method accepts a `mode` parameter to control how the island content is inserted:

```php
public function addActivity()
{
    // Replace the island content (default)
    $this->renderIsland('feed', mode: 'morph');

    // Append to the end of the island
    $this->renderIsland('feed', mode: 'append');

    // Prepend to the beginning of the island
    $this->renderIsland('feed', mode: 'prepend');

    $this->skipRender();
}
```

Available modes:
- `morph` (default) - Replaces/morphs island content entirely
- `append` - Adds content to the end of the island
- `prepend` - Adds content to the beginning of the island

### Providing scope with `with`

You can pass additional data to an island using the `with` parameter. This data will be available as variables within the island template:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public function showNotification($message, $type)
    {
        $this->renderIsland('notification', with: [
            'message' => $message,
            'type' => $type,
        ]);

        $this->skipRender();
    }
};
?>
```

```blade
<div>
    @island(name: 'notification')
        <div class="alert alert-{{ $type }}">
            {{ $message }}
        </div>
    @endisland

    <button wire:click="showNotification('Success!', 'success')">
        Show Notification
    </button>
</div>
```

The `with` data takes precedence over component properties, allowing you to override values for specific island renders.

### Default scope on the island

You can provide default values for the `with` scope directly on the island directive. These defaults are used when the island renders normally, but can be overridden by the imperative `with` parameter:

```blade
<?php

use Livewire\Component;

new class extends Component
{
    public $count = 10;

    public function resetCounter()
    {
        // Override the default 'count' from the directive
        $this->renderIsland('counter', with: ['count' => 0]);
        $this->skipRender();
    }
};
?>

<div>
    @island(name: 'counter', with: ['count' => $this->count])
        <div>Count: {{ $count }}</div>
    @endisland

    <button wire:click="resetCounter">Reset to Zero</button>
</div>
```

The scope precedence order is:
1. Runtime `with` from `renderIsland()` (highest priority)
2. Directive `with` from `@island(with: [...])`
3. Component properties (lowest priority)

## Streaming to islands

The `streamIsland()` method provides real-time streaming updates to islands. Unlike `renderIsland()`, which includes the island in the response, `streamIsland()` immediately sends the content to the browser as soon as it's called.

### Basic streaming

Here's an example of streaming activity updates as they happen:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public function streamUpdates()
    {
        // Stream content to the island immediately
        $this->streamIsland('activity', '<div>New sale: $500</div>', mode: 'append');

        // Skip rendering the rest of the component
        $this->skipRender();
    }
};
?>
```

```blade
<div>
    @island(name: 'activity')
        <!-- Activity items will be streamed here -->
    @endisland

    <button wire:click.async="streamUpdates">Stream Updates</button>
</div>
```

> [!tip] Use `.async` with streaming
> Streaming works best with async actions (`wire:click.async`) since you typically want the stream to start immediately without waiting for other requests.

### Multiple stream calls

You can call `streamIsland()` multiple times within a single action. This is perfect for streaming responses piece by piece, like a chat interface:

```php
<?php

use Livewire\Component;
use App\Services\AiService;

new class extends Component
{
    public function streamChatResponse($prompt)
    {
        $ai = new AiService();

        // Stream each chunk as it arrives from the AI
        foreach ($ai->streamResponse($prompt) as $chunk) {
            $this->streamIsland('chat', $chunk, mode: 'append');
        }

        $this->skipRender();
    }
};
?>
```

```blade
<div>
    <div>
        @island(name: 'chat')
            <!-- Chat response will stream here word by word -->
        @endisland
    </div>

    <button wire:click.async="streamChatResponse('Tell me a story')">
        Start Streaming
    </button>
</div>
```

Each call to `streamIsland()` with `mode: 'append'` will add content to the island in real-time as the AI generates it.

### Streaming with scope

Like `renderIsland()`, you can pass data to `streamIsland()` using the `with` parameter:

```php
public function streamNotifications()
{
    foreach ($this->getLatestNotifications() as $notification) {
        $this->streamIsland('notifications', with: [
            'title' => $notification->title,
            'body' => $notification->body,
            'timestamp' => $notification->created_at,
        ], mode: 'prepend');
    }

    $this->skipRender();
}
```

```blade
@island(name: 'notifications')
    <div class="notification">
        <h4>{{ $title }}</h4>
        <p>{{ $body }}</p>
        <span class="timestamp">{{ $timestamp }}</span>
    </div>
@endisland
```

The `streamIsland()` method has the same signature as `renderIsland()`:

```php
streamIsland(
    string $name,           // Island name
    ?string $content = null, // Pre-rendered content (null = render island view)
    string $mode = 'morph',  // 'morph', 'append', or 'prepend'
    array $with = []         // Additional scope data
)
```

## Considerations

While islands provide powerful isolation, keep in mind:

**Data scope**: Islands have access to the component's properties and methods, but not to template variables defined outside the island. Any `@php` variables or loop variables from the parent template won't be available inside the island:

```blade
@php
    $localVariable = 'This won\'t be available in the island';
@endphp

@island
    {{-- ❌ This will error - $localVariable is not accessible --}}
    {{ $localVariable }}

    {{-- ✅ Component properties work fine --}}
    {{ $this->revenue }}
@endisland
```

**Islands can't be used in loops or conditionals**: Because islands don't have access to loop variables or conditional context, they cannot be used inside `@foreach`, `@if`, or other control structures:

```blade
{{-- ❌ This won't work --}}
@foreach ($items as $item)
    @island
        {{ $item->name }}
    @endisland
@endforeach

{{-- ❌ This won't work either --}}
@if ($showRevenue)
    @island
        Revenue: {{ $this->revenue }}
    @endisland
@endif

{{-- ✅ Instead, put the loop/conditional inside the island --}}
@island
    @if ($this->showRevenue)
        Revenue: {{ $this->revenue }}
    @endif

    @foreach ($this->items as $item)
        {{ $item->name }}
    @endforeach
@endisland
```

**State synchronization**: Although island requests run in parallel, both islands and the root component can mutate the same component state. If multiple requests are in flight simultaneously, there can be divergent state — the last response to return will win the state battle.

**When to use islands**: Islands are most beneficial for:
- Expensive computations that shouldn't block initial page load
- Independent regions with their own interactions
- Real-time updates affecting only portions of the UI
- Performance bottlenecks in large components

Islands aren't necessary for static content, tightly coupled UI, or simple components that already render quickly.
