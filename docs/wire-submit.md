
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

        $this->redirect('/posts');
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

## Going deeper

`wire:submit` is just one of many event listeners that Livewire provides. The following two pages provide much more complete documentation on using `wire:submit` in your application:

* [Responding to browser events with Livewire](/docs/4.x/actions)
* [Creating forms in Livewire](/docs/4.x/forms)

## See also

- **[Forms](/docs/4.x/forms)** — Handle form submissions with Livewire
- **[Actions](/docs/4.x/actions)** — Process form data in actions
- **[Validation](/docs/4.x/validation)** — Validate forms before submission

## Reference

```blade
wire:submit="methodName"
wire:submit="methodName(param1, param2)"
```

### Modifiers

| Modifier | Description |
|----------|-------------|
| `.prevent` | Prevents default browser behavior (automatic for `wire:submit`) |
| `.stop` | Stops event propagation |
| `.self` | Only triggers if event originated on this element |
| `.once` | Ensures listener is only called once |
| `.debounce` | Debounces handler by 250ms (use `.debounce.500ms` for custom duration) |
| `.throttle` | Throttles handler to every 250ms minimum (use `.throttle.500ms` for custom) |
| `.window` | Listens for event on the `window` object |
| `.document` | Listens for event on the `document` object |
| `.passive` | Won't block scroll performance |
| `.capture` | Listens during the capturing phase |
| `.renderless` | Skips re-rendering after action completes |
| `.preserve-scroll` | Maintains scroll position during updates |
| `.async` | Executes action in parallel instead of queued |
