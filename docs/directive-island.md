The `@island` directive creates isolated regions within a component that update independently, without re-rendering the entire component.

## Basic usage

Wrap any portion of your template with `@island` to create an isolated region:

```blade
<?php // resources/views/components/⚡dashboard.blade.php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Revenue;

new class extends Component
{
    #[Computed]
    public function revenue()
    {
        // Expensive calculation...
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

When the "Refresh" button is clicked, only the island re-renders—the rest of the component remains untouched.

## Lazy loading islands

Defer an island's initial render until after the page loads using the `lazy` parameter:

```blade
@island(lazy: true)
    <div>
        Revenue: {{ $this->revenue }}
    </div>
@endisland
```

The island displays a loading state initially, then fetches its content in a separate request.

### Lazy vs Deferred

By default, `lazy` waits until the island is visible in the viewport. Use `defer` to load immediately after page load:

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

## Custom loading states

Use `@placeholder` to customize what displays while loading:

```blade
@island(lazy: true)
    @placeholder
        <div class="animate-pulse">
            <div class="h-32 bg-gray-200 rounded"></div>
        </div>
    @endplaceholder

    <div>
        Revenue: {{ $this->revenue }}
    </div>
@endisland
```

## Named islands

Give islands names to target them from elsewhere in your component:

```blade
@island(name: 'revenue')
    <div>Revenue: {{ $this->revenue }}</div>
@endisland

<button type="button" wire:click="$refresh" wire:island="revenue">
    Refresh revenue
</button>
```

The `wire:island` directive scopes updates to specific islands.

## Why use islands?

Islands provide performance isolation without the overhead of creating separate child components, managing props, or dealing with component communication.

**Use islands when:**
* You want to isolate expensive computations
* You need independent update regions within one component
* You want simpler architecture than nested components

[Learn more about islands →](/docs/4.x/islands)
