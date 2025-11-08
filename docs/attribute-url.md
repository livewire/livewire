The `#[Url]` attribute stores a property's value in the URL's query string, allowing users to share and bookmark specific states of a page.

## Basic usage

Apply the `#[Url]` attribute to any property that should persist in the URL:

```php
<?php // resources/views/components/user/⚡index.blade.php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\User;

new class extends Component {
    #[Url] // [tl! highlight]
    public $search = '';

    #[Computed]
    public function users()
    {
        return User::search($this->search)->get();
    }
};
?>

<div>
    <input type="text" wire:model.live="search" placeholder="Search users...">

    <ul>
        @foreach ($this->users as $user)
            <li wire:key="{{ $user->id }}">{{ $user->name }}</li>
        @endforeach
    </ul>
</div>
```

When a user types "bob" into the search field, the URL updates to `https://example.com/users?search=bob`. If they share this URL or refresh the page, the search value persists.

## How it works

The `#[Url]` attribute does two things:

1. **Writes to URL** - When the property changes, it updates the query string
2. **Reads from URL** - On page load, it initializes the property from the query string

This creates a shareable, bookmarkable state for your component.

## URL vs Session

Both `#[Url]` and `#[Session]` persist property values, but with different trade-offs:

| Feature | `#[Url]` | `#[Session]` |
|---------|----------|--------------|
| Persists across refreshes | ✅ | ✅ |
| Persists when sharing URL | ✅ | ❌ |
| Keeps URL clean | ❌ | ✅ |
| Visible to user | ✅ | ❌ |
| Shareable state | ✅ | ❌ |

Use `#[Url]` when you want users to be able to share or bookmark the current state. Use `#[Session]` when state should be private.

## Using an alias

Shorten or obfuscate property names in the URL with the `as` parameter:

```php
<?php // resources/views/components/user/⚡index.blade.php

use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    #[Url(as: 'q')] // [tl! highlight]
    public $search = '';
};
```

The URL will show `?q=bob` instead of `?search=bob`.

## Excluding values

By default, Livewire only adds query parameters when values differ from their initial value. Use `except` to customize this:

```php
<?php // resources/views/components/user/⚡index.blade.php

use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    #[Url(except: '')] // [tl! highlight]
    public $search = '';

    public function mount()
    {
        $this->search = auth()->user()->username;
    }
};
```

Now Livewire will only exclude `search` from the URL when it's an empty string, not when it equals the initial username value.

## Always show in URL

To always include the parameter in the URL, even when empty, use `keep`:

```php
#[Url(keep: true)] // [tl! highlight]
public $search = '';
```

The URL will always show `?search=` even when the value is empty.

## Nullable properties

Use nullable type hints to treat empty query parameters as `null` instead of empty strings:

```php
<?php // resources/views/components/user/⚡index.blade.php

use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    #[Url]
    public ?string $search; // [tl! highlight]
};
```

Now `?search=` sets `$search` to `null` instead of `''`.

## Browser history

By default, Livewire uses `history.replaceState()` to modify the URL without adding browser history entries. To add history entries (making the back button restore previous query values), use `history`:

```php
#[Url(history: true)] // [tl! highlight]
public $search = '';
```

Now clicking the browser's back button will restore previous search values instead of navigating to the previous page.

## When to use

Use `#[Url]` when:

* Building search or filter interfaces
* Implementing pagination
* Creating shareable views (map positions, selected filters, etc.)
* Allowing users to bookmark specific states
* Supporting browser back/forward navigation through states

## Example: Product filtering

Here's a practical example of filtering products with multiple URL parameters:

```php
<?php // resources/views/pages/⚡products.blade.php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\Product;

new class extends Component {
    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $category = 'all';

    #[Url]
    public $minPrice = 0;

    #[Url]
    public $maxPrice = 1000;

    #[Url]
    public $sort = 'name';

    #[Computed]
    public function products()
    {
        return Product::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->category !== 'all', fn($q) => $q->where('category', $this->category))
            ->whereBetween('price', [$this->minPrice, $this->maxPrice])
            ->orderBy($this->sort)
            ->paginate(20);
    }
};
?>

<div>
    <input type="text" wire:model.live="search" placeholder="Search products...">

    <select wire:model.live="category">
        <option value="all">All Categories</option>
        <option value="electronics">Electronics</option>
        <option value="clothing">Clothing</option>
    </select>

    <input type="range" wire:model.live="minPrice" min="0" max="1000">
    <input type="range" wire:model.live="maxPrice" min="0" max="1000">

    <select wire:model.live="sort">
        <option value="name">Name</option>
        <option value="price">Price</option>
        <option value="created_at">Newest</option>
    </select>

    @foreach($this->products as $product)
        <div>{{ $product->name }} - ${{ $product->price }}</div>
    @endforeach
</div>
```

Users can share URLs like:
```
https://example.com/products?q=laptop&category=electronics&minPrice=500&maxPrice=1500&sort=price
```

## SEO considerations

Query parameters are indexed by search engines and included in analytics:

* **Good for SEO** - Each unique query combination creates a unique URL that can be indexed
* **Analytics tracking** - Track which filters and searches users are using
* **Shareable on social media** - Query parameters are preserved when sharing links

## Learn more

For more information about URL query parameters, including the `queryString()` method and trait hooks, see the [URL Query Parameters documentation](/docs/4.x/url).
