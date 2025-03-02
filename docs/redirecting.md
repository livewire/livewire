After a user performs some action — like submitting a form — you may want to redirect them to another page in your application.

Because Livewire requests aren't standard full-page browser requests, standard HTTP redirects won't work. Instead, you need to trigger redirects via JavaScript. Fortunately, Livewire exposes a simple `$this->redirect()` helper method to use within your components. Internally, Livewire will handle the process of redirecting on the frontend.

If you prefer, you can use [Laravel's built-in redirect utilities](https://laravel.com/docs/responses#redirects) within your components as well.

## Basic usage

Below is an example of a `CreatePost` Livewire component that redirects the user to another page after they submit the form to create a post:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
	public $title = '';

    public $content = '';

    public function save()
    {
		Post::create([
			'title' => $this->title,
			'content' => $this->content,
		]);

		$this->redirect('/posts'); // [tl! highlight]
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

As you can see, when the `save` action is triggered, a redirect will also be triggered to `/posts`. When Livewire receives this response, it will redirect the user to the new URL on the frontend.

## Redirect to Route

In case you want to redirect to a page using its route name you can use the `redirectRoute`.

For example, if you have a page with the route named `'profile'` like this: 

```php
    Route::get('/user/profile', function () {
        // ...
    })->name('profile');
```

You can use `redirectRoute` to redirect to that page using the name of the route like so:

```php
    $this->redirectRoute('profile');
```

In case you need to pass parameters to the route you may use the second argument of the method `redirectRoute` like so:

```php
    $this->redirectRoute('profile', ['id' => 1]);
```

## Redirect to intended

In case you want to redirect the user back to the previous page they were on you can use `redirectIntended`. It accepts an optional default URL as its first argument which is used as a fallback if no previous page can be determined:

```php
    $this->redirectIntended('/default/url');
```

## Redirecting to full-page components

Because Livewire uses Laravel's built-in redirection feature, you can use all of the redirection methods available to you in a typical Laravel application.

For example, if you are using a Livewire component as a full-page component for a route like so:

```php
use App\Livewire\ShowPosts;

Route::get('/posts', ShowPosts::class);
```

You can redirect to the component by providing the component name to the `redirect()` method:

```php
public function save()
{
    // ...

    $this->redirect(ShowPosts::class);
}
```

## Flash messages

In addition to allowing you to use Laravel's built-in redirection methods, Livewire also supports Laravel's [session flash data utilities](https://laravel.com/docs/session#flash-data).

To pass flash data along with a redirect, you can use Laravel's `session()->flash()` method like so:

```php
use Livewire\Component;

class UpdatePost extends Component
{
    // ...

    public function update()
    {
        // ...

        session()->flash('status', 'Post successfully updated.');

        $this->redirect('/posts');
    }
}
```

Assuming the page being redirected to contain the following Blade snippet, the user will see a "Post successfully updated." message after updating the post:

```blade
@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif
```
