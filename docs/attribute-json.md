The `#[Json]` attribute marks an action as a JSON endpoint, returning data directly to JavaScript as a `[result, errors]` tuple. This is ideal for actions consumed by JavaScript rather than rendered in Blade.

## Basic usage

Apply the `#[Json]` attribute to any action method that returns data for JavaScript consumption:

```php
<?php // resources/views/components/⚡search.blade.php

use Livewire\Attributes\Json;
use Livewire\Component;
use App\Models\Post;

new class extends Component {
    #[Json] // [tl! highlight]
    public function search($query)
    {
        return Post::where('title', 'like', "%{$query}%")
            ->limit(10)
            ->get();
    }
};
```

```blade
<div x-data="{ query: '', posts: [] }">
    <input
        type="text"
        x-model="query"
        x-on:input.debounce="$wire.search(query).then(([data, errors]) => posts = data)"
    >

    <ul>
        <template x-for="post in posts">
            <li x-text="post.title"></li>
        </template>
    </ul>
</div>
```

The `search()` method returns posts directly to Alpine, where they're stored in the `posts` array and rendered client-side.

## The response tuple

JSON methods always return a two-element array: `[result, errors]`.

**On success:**
```js
let [data, errors] = await $wire.search()
// data = [ { id: 1, title: '...' }, ...]
// errors = null
```

**On validation failure:**
```js
let [data, errors] = await $wire.search()
// data = null
// errors = { query: ['The query field is required.'] }
```

This consistent format allows straightforward error handling:

```js
let [data, errors] = await $wire.save()

if (errors) {
    // Handle validation errors
    console.log(errors)
} else {
    // Use the data
    console.log(data)
}
```

## Behavior

The `#[Json]` attribute automatically applies two behaviors:

1. **Skips rendering** - The component doesn't re-render after the action completes, since the response is consumed by JavaScript
2. **Runs asynchronously** - The action executes in parallel without blocking other requests

These behaviors match what you'd expect from an API-style endpoint.

## When to use

Use `#[Json]` when:

* **Building dynamic search/autocomplete** - Fetching results for a dropdown or suggestion list
* **Loading data into JavaScript** - Populating charts, maps, or other JS-driven UI
* **Submitting forms with JS handling** - When you want to handle success/error states in JavaScript
* **Integrating with third-party libraries** - Providing data to libraries that manage their own rendering

> [!warning] Validation errors are isolated
> Validation errors from JSON methods are only returned in the `[result, errors]` tuple. They don't appear in `$wire.$errors` or the component's error bag. This is intentional—JSON methods are self-contained and don't affect the component's rendered state.

## See also

- **[Actions](/docs/4.x/actions)** — Learn about invoking methods and receiving return values
- **[Validation](/docs/4.x/validation)** — Server-side validation for Livewire components
- **[Async Attribute](/docs/4.x/attribute-async)** — Run actions in parallel without blocking
- **[Renderless Attribute](/docs/4.x/attribute-renderless)** — Skip re-rendering after an action
