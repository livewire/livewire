
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

## Async requests

By default, Livewire serializes actions within the same component scope: if an action is in-flight, subsequent actions are queued until the current request finishes.

Adding the `.async` modifier to `wire:click` allows actions to run in parallel instead of being queued. This is useful when you want to fire multiple requests without waiting for the previous one to complete (for example: batching background operations, optimistic UI, or rapid-fire buttons).

```html
<button type="button" wire:click.async="process">Process</button>
```

## Using `wire:click` on links

When using `wire:click` on `<a>` tags, you must append `.prevent` to prevent the default handling of a link in the browser. Otherwise, the browser will visit the provided link and update the page's URL.

```html
<a href="#" wire:click.prevent="...">
```

## Going deeper

The `wire:click` directive is just one of many different available event listeners in Livewire. For full documentation on its (and other event listeners) capabilities, visit [the Livewire actions documentation page](/docs/4.x/actions).

## Reference

```blade
wire:click="methodName"
wire:click="methodName(param1, param2)"
```

### Modifiers

**`.prevent`**
- Prevents the default browser behavior (equivalent to `event.preventDefault()`)
- Required when using `wire:click` on `<a>` tags

**`.stop`**
- Stops event propagation (equivalent to `event.stopPropagation()`)

**`.self`**
- Only triggers if the event originated on this element, not on children

**`.once`**
- Ensures the listener is only called once

**`.debounce`**
- Debounces the handler by 250ms (default)
- Use `.debounce.500ms` to specify custom duration

**`.throttle`**
- Throttles the handler to being called every 250ms minimum
- Use `.throttle.500ms` to specify custom duration

**`.async`**
- Allows the action to run in parallel instead of being queued

### Examples

```blade
<!-- Basic usage -->
<button wire:click="save">Save</button>

<!-- With parameters -->
<button wire:click="delete({{ $post->id }})">Delete</button>

<!-- Prevent default on links -->
<a href="#" wire:click.prevent="show">View Details</a>

<!-- Debounce rapid clicks -->
<button wire:click.debounce.500ms="search">Search</button>

<!-- Stop propagation -->
<div wire:click="parentAction">
    <button wire:click.stop="childAction">Click Me</button>
</div>

<!-- Async execution -->
<button wire:click.async="process">Process</button>
```

## See also

- **[Actions](/docs/4.x/actions)** — Complete guide to component actions
- **[Events](/docs/4.x/events)** — Dispatch events from click handlers
- **[wire:confirm](/docs/4.x/wire-confirm)** — Add confirmation dialogs to actions
