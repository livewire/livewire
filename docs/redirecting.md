After a user performs some action—like submitting a form—you may want to redirect them to another page in your application.

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

		return $this->redirect('/posts'); // [tl! highlight]
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

As you can see, when the `save` action is triggered, a redirect will also be triggered to `/posts`. When Livewire receives this response, it will redirect the user to the new URL on the frontend.

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

    return $this->redirect(ShowPage::class);
}
```

## Flash messages

In addition to allowing you to use Laravel's built-in redirection methods, Livewire also supports Laravel's [session flash data utilities](https://laravel.com/docs/session#flash-data).

To pass flash data along with a redirect, you can use Laravels' `->with()` method like so:

```php
use Livewire\Component;

class UpdatePost extends Component
{
    // ...

    public function update()
    {
        // ...

		return $this->redirect('/posts')->with('status', 'Post updated!');
    }
}
```

Assuming the page being redirected to contains the following Blade snippet, the user will see a "Post updated!" message after updating the post:

```blade
@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif
```

### Flashing without redirecting

Sometimes, you may want to flash data to users from the same component they're interacting with without redirecting them away.

In these cases, you can use Laravel's session data utilities directly from any Livewire action:

```php
public function update()
{
    // ...

    session()->flash('status', 'Post updated!');
}
```
