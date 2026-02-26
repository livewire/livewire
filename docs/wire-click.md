
Livewire provides a simple `wire:click` directive for calling component methods (aka actions) when a user clicks a specific element on the page.

For example, given the `ShowInvoice` component below:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;

class ShowInvoice extends Component
{
    public Invoice $invoice;

    public function download()
    {
        return response()->download(
            $this->invoice->file_path, 'invoice.pdf'
        );
    }
}
```

You can trigger the `download()` method from the class above when a user clicks a "Download Invoice" button by adding `wire:click="download"`:

```html
<button type="button" wire:click="download"> <!-- [tl! highlight] -->
    Download Invoice
</button>
```

## Passing parameters

You can pass parameters to actions directly in the `wire:click` directive:

```blade
<button wire:click="delete({{ $post->id }})">Delete</button>
```

When the button is clicked, the `delete()` method will be called with the post's ID.

> [!warning] Don't trust action parameters
> Action parameters should be treated like HTTP request input and should not be trusted. Always authorize ownership before updating data.

## Using on links

When using `wire:click` on `<a>` tags, you must append `.prevent` to prevent the default link behavior. Otherwise, the browser will navigate to the provided `href`.

```blade
<a href="#" wire:click.prevent="show">View Details</a>
```

## Preventing re-renders

Use `.renderless` to skip re-rendering the component after the action completes. This is useful for actions that only perform side effects (like logging or analytics):

```blade
<button wire:click.renderless="trackClick">Track Event</button>
```

## Preserving scroll position

By default, updating content may change the scroll position. Use `.preserve-scroll` to maintain the current scroll position:

```blade
<button wire:click.preserve-scroll="loadMore">Load More</button>
```

## Parallel execution

By default, Livewire queues actions within the same component. Use `.async` to allow actions to run in parallel:

```blade
<button wire:click.async="process">Process</button>
```

## Optimistic actions

Use `.optimistic` when you want instant UI feedback with automatic rollback on request failure:

```blade
<button wire:click.optimistic="$set('likes', likes + 1)">
    Like
</button>
```

If the request succeeds, the new value is kept. If it fails, Livewire restores the previous value.

## Going deeper

The `wire:click` directive is just one of many different available event listeners in Livewire. For full documentation on its (and other event listeners) capabilities, visit [the Livewire actions documentation page](/docs/4.x/actions).

## See also

- **[Actions](/docs/4.x/actions)** — Complete guide to component actions
- **[Events](/docs/4.x/events)** — Dispatch events from click handlers
- **[wire:confirm](/docs/4.x/wire-confirm)** — Add confirmation dialogs to actions

## Reference

```blade
wire:click="methodName"
wire:click="methodName(param1, param2)"
```

### Modifiers

| Modifier | Description |
|----------|-------------|
| `.prevent` | Prevents default browser behavior |
| `.stop` | Stops event propagation |
| `.self` | Only triggers if event originated on this element |
| `.once` | Ensures listener is only called once |
| `.debounce` | Debounces handler by 250ms (use `.debounce.500ms` for custom duration) |
| `.throttle` | Throttles handler to every 250ms minimum (use `.throttle.500ms` for custom) |
| `.window` | Listens for event on the `window` object |
| `.document` | Listens for event on the `document` object |
| `.outside` | Only listens for clicks outside the element |
| `.passive` | Won't block scroll performance |
| `.capture` | Listens during the capturing phase |
| `.camel` | Converts event name to camel case |
| `.dot` | Converts event name to dot notation |
| `.renderless` | Skips re-rendering after action completes |
| `.preserve-scroll` | Maintains scroll position during updates |
| `.async` | Executes action in parallel instead of queued |
| `.optimistic` | Enables optimistic updates with automatic rollback on failure |
