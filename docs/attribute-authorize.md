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

Certainly! Adding that clarification is important because the underlying logic relies on reflection to identify the class for model resolution.

Here is the updated documentation with a specific section on type-hinting requirements.

---

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
* **Method parameters** - It resolves the argument from the method's own parameters.

### Resolving from method parameters

When authorizing based on a method parameter, **you must type-hint the parameter**. Livewire uses the type-hint to determine which model class to use when resolving the record from the database.

```php
<?php // resources/views/components/⚡comment-manager.blade.php

use Livewire\Attributes\Authorize;
use Livewire\Component;
use App\Models\Comment;

new class extends Component {
    #[Authorize('delete', 'comment')] // [tl! highlight]
    public function deleteComment(Comment $comment) // Type-hint is required! [tl! highlight]
    {
        $comment->delete();
    }
};

```

> [!important] Type-hints are mandatory for method resolution
> If you resolve a model via a method parameter, the attribute requires a valid type-hint (e.g., `Comment $comment`). Without the type-hint, Livewire won't know which model to resolve, and the authorization check will fail.

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

## Learn more

For more information on defining abilities and policies, see the [Laravel Authorization documentation](https://laravel.com/docs/authorization).
