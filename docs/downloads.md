
File downloads in Livewire work much the same as in Laravel itself. For the most part, you can use any Laravel download utility inside a Livewire component and it should work as expected.

It's worth noting however, that under the hood, file downloads are handled differently than in a standard Laravel application. In Livewire, the file's contents are base 64 encoded, sent to the frontend, and decoded back into binary to be downloaded directly from the client.

## Basic usage

Triggering a file download in Livewire is as simple as returning a standard Laravel download response.

Here's an example of a `ShowInvoice` component that contains a "Download" button to download the invoice PDF:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Invoice;

class ShowInvoice extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function download()
    {
        return response()->download(
            $this->invoice->file_path, 'invoice.pdf'
        );
    }

    public function render()
    {
        return view('livewire.show-invoice');
    }
}
```

```html
<div>
    <h1>{{ $invoice->title }}</h1>

    <span>{{ $invoice->date }}</span>
    <span>{{ $invoice->amount }}</span>

    <button type="button" wire:click="download">Download</button>
</div>
```

Just like in a Laravel controller, you can use the storage facade to trigger downloads as well:

```php
public function download()
{
    return Storage::disk('invoices')->download('invoice.csv');
}
```

## Streaming downloads

Livewire can also stream downloads, however, they aren't truly streamed. The download isn't triggered until the entirety of its contents are collected and delivered to the content.

```php
public function download()
{
    return response()->streamDownload(function () {
        echo '...'; // echo download contents directly...
    }, 'invoice.pdf');
}
```

## Testing file downloads

Livewire also provides a dedicated assertion called `->assertFileDownloaded()` to easily test that a file was downloaded with a given name.

```php
use App\Models\Invoice;

/** @test */
public function can_download_invoice()
{
    $invoice = Invoice::factory();

    Livewire::test(ShowInvoice::class)
        ->call('download')
        ->assertFileDownloaded('invoice.pdf');
}
```
