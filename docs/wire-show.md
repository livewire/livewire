
Livewire's `wire:show` directive makes it easy to show and hide elements based on the result of an expression.

The `wire:show` directive is different than using `@if` in Blade in that it toggles an element's visibility using CSS (`display: none`) rather than removing the element from the DOM entirely. This means the element remains in the page but is hidden, allowing for smoother transitions without requiring a server round-trip.

## Basic usage

Here's a practical example of using `wire:show` to toggle a "Create Post" modal:

```php
use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    public $showModal = false;

    public $content = '';

    public function save()
    {
        Post::create(['content' => $this->content]);

        $this->reset('content');

        $this->showModal = false;
    }
}
```

```blade
<div>
    <button x-on:click="$wire.showModal = true">New Post</button>

    <div wire:show="showModal">
        <form wire:submit="save">
            <textarea wire:model="content"></textarea>

            <button type="submit">Save Post</button>
        </form>
    </div>
</div>
```

When the "Create New Post" button is clicked, the modal appears without a server roundtrip. After successfully saving the post, the modal is hidden and the form is reset.

## Checking for emptiness

Rather than writing out emptiness checks like `items.length === 0` by hand, you can use the `$empty` magic. It mirrors PHP's `empty()`, so a property reads the same on both sides of the wire:

```blade
<div wire:show="$empty('items')">
    No items yet — add your first one!
</div>

<div wire:show="! $empty('items')">
    <!-- ... -->
</div>
```

`$empty` is reactive — the element toggles as the property changes, even for client-side mutations that haven't reached the server yet.

## Using transitions

You can combine `wire:show` with Alpine.js transitions to create smooth show/hide animations. Since `wire:show` only toggles the CSS `display` property, Alpine's `x-transition` directives work perfectly with it:

```blade
<div>
    <button x-on:click="$wire.showModal = true">New Post</button>

    <div wire:show="showModal" x-transition.duration.500ms>
        <form wire:submit="save">
            <textarea wire:model="content"></textarea>
            <button type="submit">Save Post</button>
        </form>
    </div>
</div>
```

The Alpine.js transition classes above will create a fade and scale effect when the modal shows and hides.

[View the full x-transition documentation →](https://alpinejs.dev/directives/transition)

## Reference

```blade
wire:show="expression"
```

This directive has no modifiers.
