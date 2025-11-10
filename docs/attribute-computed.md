The `#[Computed]` attribute allows you to create derived properties that are cached during a request, providing a performance advantage when accessing expensive operations multiple times.

## Basic usage

Apply the `#[Computed]` attribute to any method to turn it into a cached property:

```php
<?php // resources/views/components/user/⚡show.blade.php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\User;

new class extends Component {
    public $userId;

    #[Computed] // [tl! highlight]
    public function user()
    {
        return User::find($this->userId);
    }

    public function follow()
    {
        Auth::user()->follow($this->user);
    }
};
```

```blade
<div>
    <h1>{{ $this->user->name }}</h1>
    <span>{{ $this->user->email }}</span>

    <button wire:click="follow">Follow</button>
</div>
```

The `user()` method is accessed like a property using `$this->user`. The first time it's called, the result is cached and reused for the rest of the request.

> [!info] Must use `$this` in templates
> Unlike normal properties, computed properties must be accessed via `$this` in your template. For example, `$this->posts` instead of `$posts`.

## Performance advantage

Computed properties cache their result for the duration of a request. If you access `$this->posts` multiple times, the underlying method only executes once:

```php
<?php // resources/views/components/post/⚡index.blade.php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed] // [tl! highlight]
    public function posts()
    {
        return Auth::user()->posts; // Only queries database once
    }
};
```

This allows you to freely access derived values without worrying about performance implications.

## Busting the cache

If the underlying data changes during a request, you can clear the cache using `unset()`:

```php
<?php // resources/views/components/post/⚡index.blade.php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function posts()
    {
        return Auth::user()->posts;
    }

    public function createPost()
    {
        if ($this->posts->count() > 10) {
            throw new \Exception('Maximum post count exceeded');
        }

        Auth::user()->posts()->create(...);

        unset($this->posts); // Clear cache [tl! highlight]
    }
};
```

After creating a new post, `unset($this->posts)` clears the cache so the next access retrieves the updated data.

## Caching between requests

By default, computed properties only cache within a single request. To cache across multiple requests, use the `persist` parameter:

```php
#[Computed(persist: true)] // [tl! highlight]
public function user()
{
    return User::find($this->userId);
}
```

This caches the value for 3600 seconds (1 hour). You can customize the duration:

```php
#[Computed(persist: true, seconds: 7200)] // 2 hours [tl! highlight]
```

## Caching across all components

To share cached values across all component instances in your application, use the `cache` parameter:

```php
use Livewire\Attributes\Computed;
use App\Models\Post;

#[Computed(cache: true)] // [tl! highlight]
public function posts()
{
    return Post::all();
}
```

You can optionally set a custom cache key:

```php
#[Computed(cache: true, key: 'homepage-posts')] // [tl! highlight]
```

## When to use

Computed properties are especially useful when:

* **Conditionally accessing expensive data** - Only query the database if the value is actually used in the template
* **Using inline templates** - No opportunity to pass data via `render()`
* **Omitting the render method** - Following v4's single-file component convention
* **Accessing the same value multiple times** - Automatic caching prevents redundant queries

## Limitations

> [!warning] Not supported on Form objects
> Computed properties cannot be used on `Livewire\Form` objects. Attempting to access them via `$form->property` will result in an error.

## Learn more

For detailed information about computed properties, caching strategies, and advanced use cases, see the [Computed Properties documentation](/docs/4.x/computed-properties).

## Reference

```php
#[Computed(
    bool $persist = false,
    int $seconds = 3600,
    bool $cache = false,
    ?string $key = null,
    mixed $tags = null,
)]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$persist` | `bool` | `false` | Cache the value across requests for the same component instance |
| `$seconds` | `int` | `3600` | Duration in seconds to cache the value |
| `$cache` | `bool` | `false` | Cache the value across all component instances |
| `$key` | `?string` | `null` | Custom cache key (auto-generated if not provided) |
| `$tags` | `mixed` | `null` | Cache tags (requires a cache driver that supports tags) |
