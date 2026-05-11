The `#[Authorize]` attribute integrates Laravel's Gate system directly into your Livewire actions. It ensures that an action is only executed if the user has the necessary permissions, throwing a `403 Forbidden` response otherwise.

## Basic usage

Apply the `#[Authorize]` attribute to any action method. Pass the ability name and an optional argument:

```php
<?php // resources/views/components/post/⚡edit.blade.php

use Livewire\Attributes\Authorize;
use Livewire\Component;
use App\Models\Post;

new class extends Component {
    public Post $post;

    #[Authorize('update', 'post')] // [tl! highlight]
    public function save()
    {
        $this->post->save();
    }
};
```

```blade
<button wire:click="save">
    Update Post
</button>
```

When `save()` is called, Livewire automatically checks if the current user is authorized to `update` the `$post` model stored on the component.

## Argument resolution

The attribute resolves the object to authorize against in the following order:

* **No argument** — Checks a simple gate that doesn't require a model (e.g., `#[Authorize('view-dashboard')]`).
* **Class string** — Useful for `create` permissions where no instance exists yet (e.g., `#[Authorize('create', Post::class)]`).
* **Method parameter** — Resolves the argument from the method's own parameters.
* **Component property** — Looks for a property on the component matching the argument name (e.g., `public Post $post`).

### Resolving from method parameters

When authorizing based on a method parameter, you must type-hint the parameter so Livewire knows which model to resolve:

```php
<?php // resources/views/components/⚡comment-manager.blade.php

use Livewire\Attributes\Authorize;
use Livewire\Component;
use App\Models\Comment;

new class extends Component {
    #[Authorize('delete', 'comment')] // [tl! highlight]
    public function deleteComment(Comment $comment) // [tl! highlight]
    {
        $comment->delete();
    }
};
```

> [!important]
> If you resolve a model via a method parameter, a type-hint (e.g., `Comment $comment`) is required. Without it, Livewire cannot determine which model to resolve and the authorization check will fail.

## Additional context

When authorizing actions using policies, you may pass an array as the second argument. The first element in the array will be used to determine which policy should be invoked, while the rest of the array elements are passed as parameters to the policy method.

```php
<?php

use Livewire\Attributes\Authorize;
use Livewire\Component;
use App\Models\Comment;
use App\Models\Post;

new class extends Component {
    public Post $post;

    #[Authorize('create', [Comment::class, 'post'])] // [tl! highlight]
    public function createComment() // [tl! highlight]
    {
        $this->post->comments()->create([
            'body' => 'New comment'
        ]);
    }
};
```

## Stacking multiple checks

The attribute is repeatable, so you can stack multiple authorization checks on a single method:

```php
#[Authorize('create', Post::class)]
#[Authorize('update', 'post')]
public function save()
{
    // Both checks must pass...
}
```

## When NOT to use

> [!warning]
> The `#[Authorize]` attribute only protects server-side execution of an action. It does not hide UI elements in your Blade template.

You should still use Blade's `@can` directives to hide buttons the user isn't allowed to use:

```blade
@can('update', $post)
    <button wire:click="save">Save</button>
@endcan
```

## Learn more

For more information on defining abilities and policies, see the [Laravel Authorization documentation](https://laravel.com/docs/authorization).
