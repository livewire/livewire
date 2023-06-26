Getters are a way to create "derived" or "computed" properties in Livewire. Like accessors on an Eloquent model, getters allow you to access values and cache them for future access during the request.

Getters are particularly useful in combination with a component's properties.

## Basic usage

To create a getter, you can add the `#[Getter]` attribute above any method in your Livewire component. Once the attribute has been added to the method, you can access it like any other property.

For example, here's a `ShowUser` component that uses a getter named `user` to access a `User` Eloquent model based on a property named `$userId`:

```php
<?php

use Livewire\Attributes\Getter;
use Livewire\Component;
use App\Models\User;

class ShowUser extends Component
{
    public $userId;

    #[Getter]
    public function user()
    {
        return User::find($this->userId);
    }

    public function follow()
    {
        Auth::user()->follow($this->user);
    }

    public function render()
    {
        return view('livewire.show-user');
    }
}
```

```blade
<div>
    <h1>{{ $this->user->name }}</h1>

    <span>{{ $this->user->email }}</span>

    <button wire:click="follow">Follow</button>
</div>
```

Because the `#[Getter]` attribute has been added to the `user()` method, the value is accessible in other methods in the component and the Blade template.

> [!info] Must use `$this` in your template
> Unlike normal properties, getters aren't directly available inside your component's template. Instead, you must access them on the `$this` object. For example, a getter called `posts()` must be accessed via `$this->posts` inside your template.

## Performance advantage

You may be asking yourself: why use getters at all? Why not just call the method directly?

Accessing a method as a getter offers a performance advantage over calling a method. Internally, when a getter is executed for the first time, Livewire caches the returned value. This way, any subsequent accesses in the request will return the cached value instead of executing multiple times.

This allows you to freely access a derived value and not worry about the performance implications.

> [!warning] Getters are only cached for a single request
> It's a common misconception that Livewire caches getters for the entire lifespan of your Livewire component on a page. However, this isn't the case. Instead, Livewire only caches the getter for the duration of a single component request. This means that if your getter contains an expensive database query, it will be executed every time your Livewire component performs an update.

### Busting the cache

Consider the following problematic scenario:
1) You access a getter that depends on a certain property or database state
2) The underlying property or database state changes
3) The cached value for the getter becomes stale and needs to be re-computed

To clear, or "bust", the stored cache of a getter, you can use PHP's `unset()` function.

Below is an example of an action called `createPost()` that, by creating a new post in the application, makes the `posts()` getter stale—meaning the `posts` getter needs to be re-computed to include the newly added post:

```php
<?php

use Livewire\Attributes\Getter;
use Livewire\Component;

class ShowPosts extends Component
{
    public function createPost()
    {
        if ($this->posts->count() > 10) {
            throw new \Exception('Maximum post count exceeded');
        }

        Auth::user()->posts()->create(...);

        unset($this->posts); // [tl! highlight]
    }

    #[Getter]
    public function posts()
    {
        return Auth::user()->posts;
    }

    // ...
}
```

In the above component, the getter is cached before a new post is created because the `createPost` method accesses `$this->posts` before the new post is created. To ensure that `$this->posts` contains the most up-to-date contents when accessed inside the view, the cache is invalidated using `unset($this->posts)`.

### Caching a getter between requests

Sometimes you would like to cache the value of a getter for the lifespan of a Livewire component, rather than it being cleared after every request. In these cases, you can use [Laravel's caching utilities](https://laravel.com/docs/cache#retrieve-store).

Below is an example of a `user()` getter, where instead of executing the Eloquent query directly, we wrap the query in `Cache::remember()` to ensure that any future requests retrieve it from Laravel's cache instead of re-executing the query:

```php
<?php

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Getter;
use Livewire\Component;
use App\Models\User;

class ShowUser extends Component
{
    public $userId;

    #[Getter]
    public function user()
    {
        $key = 'user'.$this->getId();
        $seconds = 3600; // 1 hour...

        return Cache::remember($key, $seconds, function () {
            return User::find($this->userId);
        });
    }

    // ...
}
```

Because each unique instance of a Livewire component has a unique ID, we can use `$this->getId()` to generate a unique cache key that will only be applied to future requests for this same component instance.

Because most of this added code is boilerplate in that it's predictable and can easily be abstracted, Livewire provides a helpful `persist` parameter. By applying `[Getter(persist: true)]` to a method, you can achieve the same result without any extra code:

```php
use Livewire\Attributes\Getter;
use App\Models\User;

#[Getter(persist: true)]
public function user()
{
    return User::find($this->userId);
}
```

In the example above, when `$this->user` is accessed from your component, it will continue to be cached for the duration of the Livewire component on the page. This means the actual Eloquent query will only be executed once.

Livewire caches persisted values for 3600 seconds (one hour). You can override this default by passing an additional `seconds` parameter to the `#[Getter]` attribute:

```php
#[Getter(persist: true, seconds: 7200)]
```

> [!tip] Calling `unset()` will bust this cache
> As previously discussed, you can clear a getter's cache using PHP's `unset()` method. This also applies to the `persist: true` parameter. When calling `unset()` on a cached getter, Livewire will clear not only the getter cache but also the underlying cached value in the Laravel cache.


## When to use Getters?

In addition to offering performance advantages, there are a few other scenarios where getters are helpful.

Specifically, when passing data into your component's Blade template, there are a few occasions where a getter is a better alternative. Below is an example of a simple component's `render()` method passing a collection of `posts` to a Blade template:

```php
public function render()
{
    return view('livewire.show-posts', [
        'posts' => Post::all(),
    ]);
}
```

```blade
<div>
    @foreach ($posts as $post)
        <!-- ... -->
    @endforeach
</div>
```

Although this is sufficient for many use cases, here are three scenarios where a getter would be a better alternative:

### Conditionally accessing values

If you are conditionally accessing a value that is computationally expensive to retrieve in your Blade template, you can reduce performance overhead using a getter.

Consider the following template without a getter:

```blade
<div>
    @if (Auth::user()->can_see_posts)
        @foreach ($posts as $post)
            <!-- ... -->
        @endforeach
    @endif
</div>
```

If a user is restricted from viewing posts, the database query to retrieve the posts has already been made, yet the posts are never used in the template.

Here's a version of the above scenario using a getter instead:

```php
use Livewire\Attributes\Getter;
use App\Models\Post;

#[Getter]
public function posts()
{
    return Post::all();
}

public function render()
{
    return view('livewire.show-posts');
}
```

```blade
<div>
    @if (Auth::user()->can_see_posts)
        @foreach ($this->posts as $post)
            <!-- ... -->
        @endforeach
    @endif
</div>
```

Now, because we are providing the posts to the template using a getter, we only execute the database query when the data is needed.

### Using inline templates

Another scenario when getters are helpful is using [inline templates](/docs/components#inline-components) in your component.

Below is an example of an inline component where, because we are returning a template string directly inside `render()`, we never have an opportunity to pass data into the view:

```php
<?php

use Livewire\Attributes\Getter;
use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    #[Getter]
    public function posts()
    {
        return Post::all();
    }

    public function render()
    {
        return <<<HTML
        <div>
            @foreach ($this->posts as $post)
                <!-- ... -->
            @endforeach
        </div>
        HTML;
    }
}
```

In the above example, without a getter, we would have no way to explicitly pass data into the Blade template.

### Omitting the render method

In Livewire, another way to cut down on boilerplate in your components is by omitting the `render()` method entirely. Livewire will use its own `render()` method returning the corresponding Blade view by convention.

In these case, you obviously don't have a `render()` method from which you can pass data into a Blade view.

Rather than re-introducing the `render()` method into your component, you can instead provide that data to the view via getters:

```php
<?php

use Livewire\Attributes\Getter;
use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    #[Getter]
    public function posts()
    {
        return Post::all();
    }
}
```

```blade
<div>
    @foreach ($this->posts as $post)
        <!-- ... -->
    @endforeach
</div>
```

