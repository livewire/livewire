
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

## Using `wire:click` on links

When using `wire:click` on `<a>` tags, you must append `.prevent` to prevent the default handling of a link in the browser. Otherwise, the browser will visit the provided link and update the page's URL.

```html
<a href="#" wire:click.prevent="...">
```

## Going deeper

The `wire:click` directive is just one of many different available event listeners in Livewire. For full documentation on its (and other event listeners) capabilities, visit [the Livewire actions documentation page](/docs/actions).
