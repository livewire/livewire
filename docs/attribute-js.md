The `#[Js]` attribute designates methods that return JavaScript code to be executed on the client-side, providing a way to trigger client-side behavior from server-side actions.

## Basic usage

Apply the `#[Js]` attribute to methods that return JavaScript expressions:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Attributes\Js;
use Livewire\Component;
use App\Models\Post;

new class extends Component
{
    public $title = '';

    public function save()
    {
        Post::create(['title' => $this->title]);

        return $this->showSuccessMessage(); // [tl! highlight]
    }

    #[Js] // [tl! highlight:start]
    public function showSuccessMessage()
    {
        return "alert('Post saved successfully!')";
    } // [tl! highlight:end]
};
```

When the `save()` action completes, the JavaScript expression `alert('Post saved successfully!')` will execute on the client.

## Alternative: Using js() method

Instead of the `#[Js]` attribute, you can use the `js()` method for one-off JavaScript expressions:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Component;
use App\Models\Post;

new class extends Component
{
    public $title = '';

    public function save()
    {
        Post::create(['title' => $this->title]);

        $this->js("alert('Post saved successfully!')"); // [tl! highlight]
    }
};
```

The `js()` method is more concise for simple expressions, while `#[Js]` methods are better for reusable or complex JavaScript logic.

## Accessing $wire

You can access the component's `$wire` object inside JavaScript expressions:

```php
#[Js]
public function resetForm()
{
    return <<<'JS'
        $wire.title = ''
        $wire.content = ''
        alert('Form has been reset')
    JS;
}
```

## When to use

Use `#[Js]` when you need to:

* Show client-side alerts or notifications after server actions
* Trigger JavaScript animations or transitions
* Update client-side state without re-rendering
* Execute reusable JavaScript logic from multiple places
* Integrate with third-party JavaScript libraries

## JavaScript actions vs #[Js] methods

There's an important distinction:

* **JavaScript actions** (`$js.methodName`) run entirely on the client without making a server request
* **`#[Js]` methods** run on the server first, then execute the returned JavaScript on the client

```php
<?php // resources/views/components/⚡example.blade.php

use Livewire\Attributes\Js;
use Livewire\Component;

new class extends Component
{
    public $count = 0;

    // Server-side method that returns JavaScript
    #[Js]
    public function showCount()
    {
        return "alert('Count is: {$this->count}')";
    }
};
```

```blade
<div>
    <button wire:click="showCount">Show Count</button>
</div>

<script>
    // Pure client-side JavaScript action
    this.$js.incrementLocal = () => {
        console.log('No server request made')
    }
</script>
```

## Learn more

For more information about JavaScript integration in Livewire, see:

* [JavaScript documentation](/docs/4.x/javascript)
* [JavaScript actions documentation](/docs/4.x/actions#javascript-actions)
