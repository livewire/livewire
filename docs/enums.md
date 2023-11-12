
Enums are a powerful feature introduced in PHP 8.1 for representing a fixed set of values in your codebase. They are often useful for things like statuses, countries, and other fixed datasets.

Livewire supports setting [Backed Enums](https://www.php.net/manual/en/language.enumerations.backed.php) as properties in your components and will automatically take care of serializing them by their backing string between requests.

## Basic usage

Here is an example of an `InvoiceStatus` Enum being used inside a `ShowInvoice` component to represent and display the status of the invoice on the page:

```php
<?php

namespace App\Enums;

enum InvoiceStatus: string {
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PAID = 'paid';
    case CANCELED = 'canceled';
}
```

The above Enum can be set to a Livewire property just like any other value and Livewire will handle serializing it between requests:

```php
<?php

use App\Enums\InvoiceStatus;
use Livewire\Component;

class ShowInvoice extends Component
{
    public InvoiceStatus $status; // [tl! highlight]

    public function mount(Invoice $invoice)
    {
        $this->recipient = $invoice->recipient;
        $this->status = $invoice->status; // [tl! highlight]
        $this->amount = $invoice->amount;
    }

    public function render()
    {
        return view('livewire.show-invoice');
    }
}
```

You can reference the `$status` property as an instance of the `InvoiceStatus` Enum in your Blade view however you like:

```html
<div>
    <p>Reipient: {{ $recipient }}</p>
    <p>Status: {{ $status->name }}</p> <!-- [tl! highlight] -->
    <p>Amount: {{ $amount }}</p>
</div>
```

## Associating descriptions with cases

Often, select dropdowns and other inputs allow users to choose between different Enum values in your component. In these cases, you often need to display a human-friendly description of the Enum value in the interface, rather than the machine-oriented backing value.

To make this experience pleasant, Livewire provides a `Describable` trait and a `Description` attribute that can be used to associate human-friendly descriptions with Enum cases.

Let's look at an `UpdateProfile` Livewire component that allows a user to select the country they reside in via a select dropdown.

Here's a `Country` Enum, using Livewire's Describable Enums feature to store a list of countries in your application:

```php
<?php

namespace App\Enums;

use Livewire\Enums\Describable;
use Livewire\Enums\Description;

enum Country: string {
    use Describable; // [tl! highlight]

    #[Description('United States')] // [tl! highlight]
    case US = 'US';

    #[Description('Canada')] // [tl! highlight]
    case CA = 'CA';

    // ...
}
```

As you can see, each country is represented by its country-code for use throughout your codebase and database. Because users expect a more readable representation of countries, the `#[Description]` attribute has been used to associate the name of the country with each of these entries.

Below is the `UpdateProfile` component that represents the `Country` Enum as a `$country` property to allow a user to change their country and update it in the database:

```php
<?php

use App\Enums\Country;
use Livewire\Component;

class UpdateProfile extends Component
{
    public $username;
    public Country $country; // [tl! highlight]

    public function mount()
    {
        $this->username = auth()->user()->username;
        $this->country = auth()->user()->country; // [tl! highlight]
    }

    public function save()
    {
        // Validate and authorize:
        // ..

        // Persist the user:
        $user = auth()->user();
        $user->username = $this->username;
        $user->country = $this->country; // [tl! highlight]
        $user->save();
    }

    public function render()
    {
        return view('livewire.show-invoice');
    }
}
```

From this component's Blade view, the Enums can be iterated on to provide a list of countries as the options in a select menu. You'll notice the description set earlier can be retrieved using the `->description()` method that is provided by the `Livewire\Enums\Describable` trait:

```html
<form wire:submit="save">
    <input wire:model="username" type="text">

    <select wire:model="country"> <!-- [tl! highlight:4] -->
        @foreach (\App\Enums\Country::cases() as $country)
            <option value="{{ $country->value }}">{{ $country->description() }}</option>
        @endforeach
    </select>

    <button type="submit">Save</button>
</form>
```

With the above setup, a user can browse a list of country names easily, and the underlying country code will be used to store in the database.

Livewire's `Describable` trait and `#[Description]` attribute make it simple to store these different representations side-by-side in your codebase.
