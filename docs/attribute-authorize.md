The `#[Authorize]` attribute integrates Laravel’s Gate system directly into your Livewire actions. It ensures that an action is only executed if the user has the necessary permissions, throwing a `403 Forbidden` response otherwise.

## Basic usage

Apply the `#[Authorize]` attribute to any action method. You can pass the ability name and an optional argument:

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

The attribute is intelligent about how it resolves the object you want to authorize. It checks for arguments in the following order:

* **No argument** - Checks a simple gate that doesn't require a model (e.g., `#[Authorize('view-dashboard')]`).
* **Class strings** - Useful for 'create' permissions where no instance exists yet (e.g., `#[Authorize('create', Post::class)]`).
* **Component properties** - It looks for a property on the component matching the argument name (e.g., `public $post`).
* **Method parameters** - If not found on the component, it resolves the argument from the method's own parameters (including Route Model Binding resolution).

### Example: Resolving from method parameters

```php
<?php // resources/views/components/⚡comment-manager.blade.php

use Livewire\Attributes\Authorize;
use Livewire\Component;
use App\Models\Comment;

new class extends Component {
    #[Authorize('delete', 'comment')] // [tl! highlight]
    public function deleteComment(Comment $comment)
    {
        $comment->delete();
    }
};
```

---

## When to use

Use `#[Authorize]` to centralize your security logic and keep your action methods focused on their primary task:

* **Resource protection** - Ensuring a user can only modify or delete records they own.
* **Action-specific restrictions** - Restricting sensitive operations like exporting data or changing site settings.
* **Cleaner code** - Removing boilerplate `Gate::authorize()` or `$this->authorize()` calls from the beginning of every method.

---

## When NOT to use

> [!warning] Authorization is not UI masking
> **The `#[Authorize]` attribute only protects the server-side execution of an action.** It does not automatically hide UI elements in your Blade template.

Even if an action is protected, you should still use Blade's `@can` directives to prevent users from seeing buttons they aren't allowed to use:

```blade
{{-- The action is secure, but the button should still be hidden for UX --}}
@can('update', $post)
    <button wire:click="save">Save</button>
@endcan
```

## Advanced: Dynamic resolution

If you pass a string as the second argument, Livewire first checks if a property with that name exists on your component. if not, it inspects the method's parameters:

```php
#[Authorize('update', 'item')]
public function update(Item $item) { ... }
```

In this scenario, the attribute identifies the `$item` parameter, resolves the model from the incoming request data, and passes it to the Gate.

## Learn more

For more information on defining abilities and policies, see the [Laravel Authorization documentation](https://laravel.com/docs/authorization).
