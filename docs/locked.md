Livewire properties are able to be modified freely on both the frontend and backend using utilities like `wire:model`. If you want to prevent a property — like a model ID — from being modified on the frontend, you can use Livewire's `#[Locked]` attribute.

## Basic usage

Below is a `ShowPost` component that stores a `Post` model's ID as a public property named `$id`. To keep this property from being modified by a curious or malicious user, you can add the `#[Locked]` attribute to the property:

> [!warning] Make sure you import attribute classes
> Make sure you import any attribute classes. For example, the below `#[Locked]` attribute requires the following import `use Livewire\Attributes\Locked;`.

```php
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowPost extends Component
{
	#[Locked] // [tl! highlight]
    public $id;

    public function mount($postId)
    {
        $this->id = $postId;
    }

	// ...
}
```

By adding the `#[Locked]` attribute, you are ensured that the `$id` property will never be tampered with.

> [!tip] Model properties are secure by default
> If you store an Eloquent model in a public property instead of just the model's ID, Livewire will ensure the ID isn't tampered with, without you needing to explicitly add the `#[Locked]` attribute to the property. For most cases, this is a better approach than using `#[Locked]`:
> ```php
> class ShowPost extends Component
> {
>    public Post $post; // [tl! highlight]
>
>    public function mount($postId)
>    {
>        $this->post = Post::find($postId);
>    }
>
>	// ...
>}
> ```

### Why not use protected properties?

You might ask yourself: why not just use protected properties for sensitive data?

Remember, Livewire only persists public properties between network requests. For static, hard-coded data, protected properties are suitable. However, for data that is stored at runtime, you must use a public property to ensure that the data is persisted properly.

### Can't Livewire do this automatically?

In a perfect world, Livewire would lock properties by default, and only allow modifications when `wire:model` is used on that property.

Unfortunately, that would require Livewire to parse all of your Blade templates to understand if a property is modified by `wire:model` or a similar API.

Not only would that add technical and performance overhead, it would be impossible to detect if a property is mutated by something like Alpine or any other custom JavaScript.

Therefore, Livewire will continue to make public properties freely mutable by default and give developers the tools to lock them as needed.
