The `#[Locked]` attribute prevents properties from being modified on the client-side, protecting sensitive data like model IDs from tampering by users.

## Basic usage

Apply the `#[Locked]` attribute to any public property that should not be changed from the frontend:

```php
<?php // resources/views/components/post/⚡show.blade.php

use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Models\Post;

new class extends Component
{
    #[Locked] // [tl! highlight]
    public $postId;

    public function mount($id)
    {
        $this->postId = $id;
    }

    public function delete()
    {
        Post::find($this->postId)->delete();

        return redirect('/posts');
    }
};
```

If a user attempts to modify a locked property through browser DevTools or by tampering with requests, Livewire will throw an exception and prevent the action from executing.

> [!warning] Backend modifications still allowed
> Properties with the `#[Locked]` attribute can still be changed in your component's PHP code. The lock only prevents client-side tampering. Be careful not to pass untrusted user input to locked properties in your own methods.

## When to use

Use `#[Locked]` when you need to:

* Store model IDs that should never be changed by users
* Preserve authorization-sensitive data throughout component lifecycle
* Protect any public property that acts as a security boundary

> [!tip] Model properties are secure by default
> If you store an Eloquent model in a public property, Livewire automatically ensures the ID isn't tampered with—no `#[Locked]` attribute needed:
> ```php
> <?php // resources/views/components/post/⚡show.blade.php
>
> use Livewire\Component;
> use App\Models\Post;
>
> new class extends Component
> {
>     public Post $post; // Already protected [tl! highlight]
>
>     public function mount($id)
>     {
>         $this->post = Post::find($id);
>     }
> };
> ```

## Why not protected properties?

You might wonder why you can't just use `protected` properties for sensitive data.

Remember, Livewire only persists public properties between requests. Protected properties work fine for static, hard-coded values, but any data that needs to be stored at runtime must use a public property to persist properly between requests.

This is where `#[Locked]` becomes essential: it gives you the persistence of public properties with protection against client-side tampering.

## Can't Livewire do this automatically?

In a perfect world, Livewire would lock properties by default and only allow modifications when `wire:model` is used on that property.

Unfortunately, that would require Livewire to parse all of your Blade templates to understand if a property is modified by `wire:model` or a similar API.

Not only would that add technical and performance overhead, it would be impossible to detect if a property is mutated by something like Alpine or any other custom JavaScript.

Therefore, Livewire will continue to make public properties freely mutable by default and give developers the tools to lock them as needed.
