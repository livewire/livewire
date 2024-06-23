
Livewire makes it easy to persist property values across page refreshes/changes using the `#[Session]` attribute.

By adding `#[Session]` to a property in your component, Livewire will store that property's value in the session every time it changes. This way, when a page is refreshed, Livewire will fetch the latest value from the session and use it in your component.

The `#[Session]` attribute is analogous to the [`#[Url]`](/docs/url) attribute. They are both useful in similar scenarios. The primary difference being `#[Session]` persists values without modifying the URL's query string, which is sometimes desired; sometimes not.

## Basic usage

Here's a `ShowPosts` component that allows users to filter visible posts by a string stored in a `$search` property:

```php
<?php

use Livewire\Attributes\Session;
use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    #[Session] // [tl! highlight]
    public $search;

    protected function posts()
    {
        return $this->search === ''
            ? Post::all()
            : Post::where('title', 'like', '%'.$this->search.'%');
    }

    public function render()
    {
        return view('livewire.show-posts', [
            'posts' => $this->posts(),
        ]);
    }
}
```

Because the `#[Session]` attribute has been added to the `$search` property, after a user enters a search value, they can refresh the page and the search value will be persisted. Every time `$search` is updated, its new value will be stored in the user's session and used across page loads.

> [!warning] Performance implications
> Because Laravel sessions are loaded into memory during every request, you can slow down the performance of your entire application for a given user by storing too much in a user's session.

## Setting a custom key

When using `[#Session]`, Livewire will store the property value in the session using a dynamically generated key that consists of the component name combined with the property name.

This ensures that properties across component instances will use the same session value. It also ensures properties of the same name from different components won't conflict.

If you want full control over what session key Livewire uses for a given property, you can pass the `key:` parameter:

```php
<?php

use Livewire\Attributes\Session;
use Livewire\Component;

class ShowPosts extends Component
{
    #[Session(key: 'search')] // [tl! highlight]
    public $search;

    // ...
}
```

When Livewire stores and retrieves the value of the `$search` property, it will use the given key: "search".

Additionally, if you want to generate the key dynamically from other properties in your component, you can do so using the following curly brace notation:

```php
<?php

use Livewire\Attributes\Session;
use Livewire\Component;
use App\Models\Author;

class ShowPosts extends Component
{
    public Author $author;

    #[Session(key: 'search-{author.id}')] // [tl! highlight]
    public $search;

    // ...
}
```

In the above example, if the `$author` model's id is "4", the session key will become: `search-4`
