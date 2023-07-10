Livewire allows you to store component properties in the URL's query string. For example, you may want a `$search` property in your component to be included in the URL: `https://example.com/users?search=bob`. This is particularly useful for things like filtering, sorting, and pagination, as it allows users to share and bookmark specific states of a page.

## Basic usage

Below is a `ShowUsers` component that allows you to search users by their name via a simple text input:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\User;

class ShowUsers extends Component
{
    public $search = '';

    public function render()
    {
        return view('livewire.show-users', [
            'users' => User::search($this->search)->get(),
        ]);
    }
}
```

```blade
<div>
    <input type="text" wire:model.live="search">

    <ul>
        @foreach ($users as $user)
            <li>{{ $user->name }}</li>
        @endforeach
    </ul>
</div>
```

As you can see, because the text input uses `wire:model.live="search"`, as a user types into the field, network requests will be sent to update the `$search` property and show a filtered set of users on the page.

However, if the visitor refreshes the page, the search value and results will be lost.

To preserve the search value across page loads so that a visitor can refresh the page or share the URL, we can store the search value in the URL's query string by adding the `#[Url]` attribute above the `$search` property like so:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\User;

class ShowUsers extends Component
{
    #[Url] // [tl! highlight]
    public $search = '';

    public function render()
    {
        return view('livewire.show-users', [
            'posts' => User::search($this->search)->get(),
        ]);
    }
}
```

Now, if a user types "bob" into the search field, the URL bar in the browser will show:

```
https://example.com/users?search=bob
```

If they now load this URL from a new browser window, "bob" will be filled in the search field, and the user results will be filtered accordingly.

## Initializing properties from the URL

As you saw in the previous example, when a property uses `#[Url]`, not only does it store its updated value in the query string of the URL, it also references any existing query string values on page load.

For example, if a user visits the URL `https://example.com/users?search=bob`, Livewire will set the initial value of `$search` to "bob".

```php
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowUsers extend Component
{
    #[Url]
    public $search = ''; // Will be set to "bob"...

    // ...
}
```

## Using an alias

Livewire gives you full control over what name displays in the URL's query string. For example, you may have a `$search` property but want to either obfuscate the actual property name or shorten it to `q`.

You can specify a query string alias by providing the `as` parameter to the `#[Url]` attribute:

```php
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowUsers extend Component
{
    #[Url(as: 'q')]
    public $search = '';

    // ...
}
```

Now, when a user types "bob" into the search field, the URL will show: `https://example.com/users?q=bob` instead of `?search=bob`.

## Display on page load

By default, Livewire will only display a value in the query string after the value has been changed on the page. For example, if the default value for `$search` is an empty string: `""`, when the actual search input is empty, no value will appear in the URL.

If you want the `?search` entry to always be included in the query string, even when the value is empty, you can provide the `keep` parameter to the `#[Url]` attribute:

```php
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowUsers extend Component
{
    #[Url(keep: true)]
    public $search = '';

    // ...
}
```

Now, when the page loads, the URL will be changed to the following: `https://example.com/users?search=`

## Storing in history

By default, Livewire uses [`history.replaceState()`](https://developer.mozilla.org/en-US/docs/Web/API/History/replaceState) to modify the URL instead of [`history.pushState()`](https://developer.mozilla.org/en-US/docs/Web/API/History/pushState). This means that when Livewire updates the query string, it modifies the current entry in the browser's history state instead of adding a new one.

Because Livewire "replaces" the current history, pressing the "back" button in the browser will go to the previous page rather than the previous `?search=` value.

To force Livewire to use `history.pushState` when updating the URL, you can provide the `history` parameter to the `#[Url]` attribute:

```php
use Livewire\Component;
use Livewire\With\Url;

class ShowUsers extend Component
{
    #[Url(history: true)]
    public $search = '';

    // ...
}
```

In the example above, when a user changes the search value from "bob" to "frank" and then clicks the browser's back button, the search value (and the URL) will be set back to "bob" instead of navigating to the previously visited page.
