
Livewire makes it easy to persist property values across page refreshes/changes using the `#[Session]` attribute.

By adding `#[Session]` to a property in your component, Livewire will store that property's value in the session every time it changes. This way, when a page is refreshed, Livewire will fetch the latest value from the session and use it in your component.

The `#[Session]` attribute is analogous to the [`#[Url]`](/docs/url) attribute. They are both useful in similar scenarios. The primary difference is `#[Session]` persists values without modifying the URL's query string, and instead stores those values in Laravel's session cookie.

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
            ? Post::all();
            ? Post::where('title', 'like', '%'.$this->search.'%');
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

> [!warning] Use `#[Session]` sparingly
> Because Laravel sessions are cookies that are sent back and forth between every network request, you can slow down the performance of your entire application for a given user by storing too much in them. Because of this it's important to use this utility sparingly and ideally only with small datasets.
