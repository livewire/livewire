
`wire:text` is a directive that dynamically updates an element's text content based on a component property or expression. Unlike using Blade's `{{ }}` syntax, `wire:text` updates the content without requiring a network roundtrip to re-render the component.

If you are familiar with Alpine's `x-text` directive, the two are essentially the same.

## Basic usage

Here's an example of using `wire:text` to optimistically show updates to a Livewire property without waiting for a network roundtrip.

```php
use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
    public Post $post;

    public $likes;

    public function mount()
    {
        $this->likes = $this->post->like_count;
    }

    public function like()
    {
        $this->post->like();

        $this->likes = $this->post->fresh()->like_count;
    }
}
```

```blade
<div>
    <button x-on:click="$wire.likes++" wire:click="like">❤️ Like</button>

    Likes: <span wire:text="likes"></span>
</div>
```

When the button is clicked, `$wire.likes++` immediately updates the displayed count through `wire:text`, while `wire:click="like"` persists the change to the database in the background.

This pattern makes `wire:text` perfect for building optimistic UIs in Livewire.
