Livewire properties are able to modified freely on both the frontend and backend using utilities like `wire:model`. If you want to prevent a property—like a model ID—from being modified on the frontend, you can use Livewire's `#[Locked]` attribute.

Below is an `ShowPost` component that stores the post model's ID as a public property called `$id`. To keep this property from being modified by a curious or maliscous user, you can add `#[Locked]`:

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

By adding `#[Locked]`, you are ensured that the `$id` property will never be tampered with.

> [!tip] Model properties are secure by default
> If instead of storing a model's ID as a public property, you store the entire model, Livewire will ensure the ID isn't tampered with, without you needing to add `#[Locked]`. For most cases, this is a better aproach than using `#[Locked]`:
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

You might ask yourself: why not just protected properties for sensitive data?

Unfortunately, Livewire only persists public properties between network requests. For static, hardcoded data, protected properties are fine. For data that is stored at runtime, you must use a public property to ensure that data is persisted properly.

### Can't Livewire do this automatically?

In a perfect world, Livewire would lock properties by default, and only allow modifications when `wire:model` is used on that property.

Unfortunately, that would require Livewire to parse all of your Blade templates to understand if a property is modified by `wire:model` or a simlilar API.

Not only would that add technical and performance overhead, it would be impossible to detect if a property is mutated by something like AlpineJS or any other JavaScript.

Therefore, Livewire will continue to make public properties freely mutable by default, and give developers the tools to lock them as needed.
