Islands allow you to create isolated regions within a Livewire component that update independently. When an action occurs inside an island, only that island re-renders — not the entire component.

This gives you the performance benefits of breaking components into nested children without the overhead of creating separate components, managing props, or dealing with component communication.

## Basic usage

To designate an island, wrap a portion of your Blade template with the `@island` directive:

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
    @island <!-- [tl! highlight:6] -->
        <div>
            Revenue: {{ $this->revenue }}

            <button type="button" wire:click="$refresh">Refresh</button>
        </div>
    @endisland

    <div>
        <!-- Other content that won't re-render when the island updates... -->
    </div>
</div>
```

When the "Refresh" button is clicked, only the island containing the revenue calculation will re-render. The rest of the component remains untouched.

Because we placed the expensive calculation in a computed property, it will only be called when the island re-renders — not when other parts of the page update. However, since islands load with the page by default, the `revenue` property will still be calculated during the initial page load.

## Lazy loading

Sometimes you have expensive computations or slow API calls that shouldn't block your initial page load. Islands can defer their render until after the page loads by using the `lazy` parameter:

```blade
<div>
    @island(lazy: true) <!-- [tl! highlight] -->
        <div>
            Revenue: {{ $this->revenue }}

            <button type="button" wire:click="$refresh">Refresh</button>
        </div>
    @endisland

    <div>
        <!-- Other content that won't re-render when the island updates... -->
    </div>
</div>
```

The island will be empty initially, then fetch and render its content in a separate request. The expensive `revenue` computed property won't execute during the initial page load.

### Custom loading states

While a lazy island is loading, you can show custom placeholder content using the `@placeholder` directive:

```blade
@island(lazy: true)
    @placeholder <!-- [tl! highlight:4] -->
        <div class="skeleton-loader">
            <div class="animate-pulse bg-gray-200 h-32 rounded"></div>
        </div>
    @endplaceholder

    <div>
        Revenue: {{ $this->revenue }}

        <button type="button" wire:click="$refresh">Refresh</button>
    </div>
@endisland
```

The placeholder content will be shown immediately when the page loads, then replaced with the actual island content once it's fetched.

## Named islands

To trigger an island from elsewhere in your component, you can give it a name using the `name` parameter and reference it elsewhere using the `wire:island` directive:

```blade
<div>
    @island(name: 'revenue') <!-- [tl! highlight] -->
        <div>
            Revenue: {{ $this->revenue }}
        </div>
    @endisland

    <button type="button" wire:click="$refresh" wire:island="revenue"> <!-- [tl! highlight] -->
        Refresh revenue
    </button>
</div>
```

Clicking the button will trigger the `loadMore` method and a re-render of only the `activity-feed` island.

The `wire:island` directive works alongside action directives like `wire:click`, `wire:submit`, etc. to scope their updates to a specific island rather than the entire component.

## Append and prepend modes

Instead of replacing content entirely, islands can append or prepend new content. This is perfect for building pagination, infinite scroll, or real-time feeds:

```blade
<?php

use Livewire\Attributes\Paginator;
use Livewire\Component;
use App\Models\Revenue;

new class extends Component
{
    #[Paginator]
    public function items()
    {
        return Revenue::paginate();
    }

    public function loadMore()
    {
        $this->items->nextPage();
    }
};
?>

@island(name: 'feed')
    @foreach ($this->items as $item)
        <!-- ... -->
    @endforeach
@endisland

<button wire:click="items.nextPage()" wire:island.append="feed">
    Load More
</button>
```

Each click advances the paginator, renders the island and appends its new content to the existing list without re-rendering what's already there.

Available modes:
- `replace` (default) - Replace the island's content
- `append` - Add new content to the end
- `prepend` - Add new content to the beginning
