The `#[Redirect]` attribute redirects the user after an action finishes successfully. It is a declarative alternative to calling `$this->redirect()` (and related helpers) inside the method body.

## Basic usage

Apply the `#[Redirect]` attribute to any action method and pass the destination URL:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Attributes\Redirect;
use Livewire\Component;
use App\Models\Post;

new class extends Component {
    public $title = '';

    public $content = '';

    #[Redirect('/posts')] // [tl! highlight]
    public function save()
    {
        Post::create([
            'title' => $this->title,
            'content' => $this->content,
        ]);
    }
};
```

```blade
<form wire:submit="save">
    <!-- Form fields... -->

    <button type="submit">Save</button>
</form>
```

After `save()` completes, Livewire redirects the user to `/posts`. This is equivalent to calling `$this->redirect('/posts')` at the end of the method.

## Navigate (SPA redirects)

Pass `navigate: true` to use Livewire's SPA navigation instead of a full page load:

```php
#[Redirect('/posts', navigate: true)]
public function save()
{
    // ...
}
```

This maps to `$this->redirect('/posts', navigate: true)`. See the **[Navigate]('/docs/4.x/navigate')** documentation for more details.

## Redirect to a named route

Use the `route` parameter (and optional `parameters` / `absolute`) to mirror `$this->redirectRoute()`:

```php
#[Redirect(route: 'posts.index')]
public function save()
{
    // ...
}

#[Redirect(route: 'posts.show', parameters: ['post' => 1])]
public function save()
{
    // ...
}

#[Redirect(route: 'posts.index', absolute: false)]
public function save()
{
    // ...
}
```

## Redirect to a controller action

Use the `action` parameter to mirror `$this->redirectAction()`. Both string and array forms are supported:

```php
#[Redirect(action: 'PostController@index')]
public function save()
{
    // ...
}

#[Redirect(action: [PostController::class, 'index'])]
public function save()
{
    // ...
}

#[Redirect(action: [PostController::class, 'show'], parameters: ['id' => 1])]
public function save()
{
    // ...
}
```

## Redirect intended

Use `intended: true` to mirror `$this->redirectIntended()`. Optionally provide a `default` fallback URL when no intended URL is stored in the session:

```php
#[Redirect(intended: true)]
public function login()
{
    // ...
}

#[Redirect(intended: true, default: '/dashboard')]
public function login()
{
    // ...
}
```

## Mapping to the public redirect API

| **Attribute** | **Equivalent method** |
| - | - |
| `#[Redirect('/posts')]` | `$this->redirect('/posts')` |
| `#[Redirect('/posts', navigate: true)]` | `$this->redirect('/posts', navigate: true)` |
| `#[Redirect(route: 'posts.show', parameters: ['post' => 1])]` | `$this->redirectRoute('posts.show', ['post' => 1])` |
| `#[Redirect(action: [PostController::class, 'index'])]` | `$this->redirectAction([PostController::class, 'index'])` |
| `#[Redirect(intended: true, default: '/dashboard')]` | `$this->redirectIntended('/dashboard')` |

The attribute always runs **after** the action method has finished. If the method also calls `$this->redirect()` imperatively, the attribute's destination takes precedence.
