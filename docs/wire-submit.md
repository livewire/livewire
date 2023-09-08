
## Basic usage

Livewire makes it easy to handle form submissions via the `wire:submit` directive. By adding `wire:submit` to a `<form>` element, Livewire will intercept the form submission, prevent the default browser handling, and call any Livewire component method.

Here's a basic example of using `wire:submit` to handle a "Create Post" form submission:

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

        return redirect()->to('/posts');
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```blade
<form wire:submit="save"> <!-- [tl! highlight] -->
    <input type="text" wire:model="title">

    <textarea wire:model="content"></textarea>

    <button type="submit">Save</button>
</form>
```

In the above example, when a user submits the form by clicking "Save", `wire:submit` intercepts the `submit` event and calls the `save()` action on the server.

> [!info] Livewire automatically calls `preventDefault()`
> `wire:submit` is different than other Livewire event handlers in that it internally calls `event.preventDefault()` without the need for the `.prevent` modifier. This is because there are very few instances you would be listening for the `submit` event and NOT want to prevent it's default browser handling (performing a full form submission to an endpoint).

> [!info] Livewire automatically disables forms while submitting
> By default, when Livewire is sending a form submission to the server, it will disable form submit buttons and mark all form inputs as `readonly`. This way a user cannot submit the same form again until the initial submission is complete.

For more complete documentation on handling forms in Livewire, visit the [forms documentation page](/docs/forms).
