Computed properties are a way to create "derived" properties in Livewire. Like accessors on an Eloquent model, computed properties allow you to access values and cache them for future access during the request.

Computed properties are particularly useful in combination with component's public properties.

## Basic usage

To create a computed property, you can add the `#[Computed]` attribute above any method in your Livewire component. Once the attribute has been added to the method, you can access it like any other property.

> [!warning] Make sure you import attribute classes
> Make sure you import any attribute classes. For example, the below `#[Computed]` attribute requires the following import `use Livewire\Attributes\Computed;`.

For example, here's a `ShowUser` component that uses a computed property named `user()` to access a `User` Eloquent model based on a property named `$userId`:

```php
<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\User;

class ShowUser extends Component
{
    public $userId;

    #[Computed]
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

Because the `#[Computed]` attribute has been added to the `user()` method, the value is accessible in other methods in the component and within the Blade template.

> [!info] Must use `$this` in your template
> Unlike normal properties, computed properties aren't directly available inside your component's template. Instead, you must access them on the `$this` object. For example, a computed property named `posts()` must be accessed via `$this->posts` inside your template.

> [!warning] Computed properties are not supported on `Livewire\Form` objects.
> Trying to use a Computed property within a [Form](https://livewire.laravel.com/docs/forms) will result in an error when you attempt to access the property in blade using $form->property syntax.

## Performance advantage

You may be asking yourself: why use computed properties at all? Why not just call the method directly?

Accessing a method as a computed property offers a performance advantage over calling a method. Internally, when a computed property is executed for the first time, Livewire caches the returned value. This way, any subsequent accesses in the request will return the cached value instead of executing multiple times.

This allows you to freely access a derived value and not worry about the performance implications.

> [!warning] Computed properties are only cached for a single request
> It's a common misconception that Livewire caches computed properties for the entire lifespan of your Livewire component on a page. However, this isn't the case. Instead, Livewire only caches the result for the duration of a single component request. This means that if your computed property method contains an expensive database query, it will be executed every time your Livewire component performs an update.

### Busting the cache

Consider the following problematic scenario:
1) You access a computed property that depends on a certain property or database state
2) The underlying property or database state changes
3) The cached value for the property becomes stale and needs to be re-computed

To clear, or "bust", the stored cache, you can use PHP's `unset()` function.

Below is an example of an action called `createPost()` that, by creating a new post in the application, makes the `posts()` computed stale — meaning the computed property `posts()` needs to be re-computed to include the newly added post:

```php
<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
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

    #[Computed]
    public function posts()
    {
        return Auth::user()->posts;
    }

    // ...
}
```

In the above component, the computed property is cached before a new post is created because the `createPost()` method accesses `$this->posts` before the new post is created. To ensure that `$this->posts` contains the most up-to-date contents when accessed inside the view, the cache is invalidated using `unset($this->posts)`.

### Caching between requests

Sometimes you would like to cache the value of a computed property for the lifespan of a Livewire component, rather than it being cleared after every request. In these cases, you can use [Laravel's caching utilities](https://laravel.com/docs/cache#retrieve-store).

Below is an example of a computed property named `user()`, where instead of executing the Eloquent query directly, we wrap the query in `Cache::remember()` to ensure that any future requests retrieve it from Laravel's cache instead of re-executing the query:

```php
<?php

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\User;

class ShowUser extends Component
{
    public $userId;

    #[Computed]
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

But, as you may have noticed, most of this code is predictable and can easily be abstracted. Because of this, Livewire's `#[Computed]` attribute provides a helpful `persist` parameter. By applying `#[Computed(persist: true)]` to a method, you can achieve the same result without any extra code:

```php
use Livewire\Attributes\Computed;
use App\Models\User;

#[Computed(persist: true)]
public function user()
{
    return User::find($this->userId);
}
```

In the example above, when `$this->user` is accessed from your component, it will continue to be cached for the duration of the Livewire component on the page. This means the actual Eloquent query will only be executed once.

Livewire caches persisted values for 3600 seconds (one hour). You can override this default by passing an additional `seconds` parameter to the `#[Computed]` attribute:

```php
#[Computed(persist: true, seconds: 7200)]
```

> [!tip] Calling `unset()` will bust this cache
> As previously discussed, you can clear a computed property's cache using PHP's `unset()` method. This also applies to computed properties using the `persist: true` parameter. When calling `unset()` on a cached computed property, Livewire will clear not only the computed property cache, but also the underlying cached value in Laravel's cache.

## Caching across all components

Instead of caching the value of a computed property for the duration of a single component's lifecycle, you can cache the value of a computed across all components in your application using the `cache: true` parameter provided by the `#[Computed]` attribute:

```php
use Livewire\Attributes\Computed;
use App\Models\Post;

#[Computed(cache: true)]
public function posts()
{
    return Post::all();
}
```

In the above example, until the cache expires or is busted, every instance of this component in your application will share the same cached value for `$this->posts`.

If you need to manually clear the cache for a computed property, you may set a custom cache key using the `key` parameter:

```php
use Livewire\Attributes\Computed;
use App\Models\Post;

#[Computed(cache: true, key: 'homepage-posts')]
public function posts()
{
    return Post::all();
}
```

## When to use computed properties?

In addition to offering performance advantages, there are a few other scenarios where computed properties are helpful.

Specifically, when passing data into your component's Blade template, there are a few occasions where a computed property is a better alternative. Below is an example of a simple component's `render()` method passing a collection of `posts` to a Blade template:

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

Although this is sufficient for many use cases, here are three scenarios where a computed property would be a better alternative:

### Conditionally accessing values

If you are conditionally accessing a value that is computationally expensive to retrieve in your Blade template, you can reduce performance overhead using a computed property.

Consider the following template without a computed property:

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

Here's a version of the above scenario using a computed property instead:

```php
use Livewire\Attributes\Computed;
use App\Models\Post;

#[Computed]
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

Now, because we are providing the posts to the template using a computed property, we only execute the database query when the data is needed.

### Using inline templates

Another scenario when computed properties are helpful is using [inline templates](/docs/components#inline-components) in your component.

Below is an example of an inline component where, because we are returning a template string directly inside `render()`, we never have an opportunity to pass data into the view:

```php
<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    #[Computed]
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

In the above example, without a computed property, we would have no way to explicitly pass data into the Blade template.

### Omitting the render method

In Livewire, another way to cut down on boilerplate in your components is by omitting the `render()` method entirely. When omitted, Livewire will use its own `render()` method returning the corresponding Blade view by convention.

In these case, you obviously don't have a `render()` method from which you can pass data into a Blade view.

Rather than re-introducing the `render()` method into your component, you can instead provide that data to the view via computed properties:

```php
<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    #[Computed]
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
