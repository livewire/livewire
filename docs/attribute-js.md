The `#[Js]` attribute designates methods that return JavaScript code to be executed on the client-side. Methods marked with `#[Js]` can be called directly from your templates without making a server request.

## Basic usage

Apply the `#[Js]` attribute to methods that return JavaScript expressions:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Attributes\Js;
use Livewire\Component;

new class extends Component {
    public $title = '';
    public $content = '';

    #[Js] // [tl! highlight:start]
    public function resetForm()
    {
        return <<<'JS'
            $wire.title = ''
            $wire.content = ''
        JS;
    } // [tl! highlight:end]
};
```

```blade
<form wire:submit="save">
    <input wire:model="title" placeholder="Title">
    <textarea wire:model="content" placeholder="Content"></textarea>

    <button type="submit">Save</button>
    <button type="button" @click="$wire.resetForm()">Reset</button> <!-- [tl! highlight] -->
</form>
```

When `$wire.resetForm()` is called, the JavaScript executes directly in the browser — no server round-trip occurs.

## Executing JavaScript after server actions

If you need to execute JavaScript **after a server action completes**, use the `js()` method instead:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Component;
use App\Models\Post;

new class extends Component {
    public $title = '';

    public function save()
    {
        Post::create(['title' => $this->title]);

        $this->js("alert('Post saved successfully!')"); // [tl! highlight]
    }
};
```

The `js()` method queues JavaScript to be executed when the server response arrives.

## Accessing $wire

You can access the component's `$wire` object inside JavaScript expressions:

```php
#[Js]
public function resetForm()
{
    return <<<'JS'
        $wire.title = ''
        $wire.content = ''
    JS;
}
```

## When to use

Use `#[Js]` when you need to:

* Reset or clear form fields without server overhead
* Trigger JavaScript animations or transitions
* Update client-side state without re-rendering
* Execute reusable JavaScript logic from multiple places
* Integrate with third-party JavaScript libraries

## JavaScript actions vs #[Js] methods

There's an important distinction:

* **`#[Js]` methods** are defined in PHP and return JavaScript code. They are called via `$wire.methodName()` without making a server request.
* **JavaScript actions** (`$js.methodName`) are defined entirely in JavaScript using `@script` blocks.

Both approaches execute JavaScript on the client without a server round-trip. The difference is where the JavaScript code is defined.

```php
<?php // resources/views/components/⚡example.blade.php

use Livewire\Attributes\Js;
use Livewire\Component;

new class extends Component {
    public $count = 0;

    // JavaScript defined in PHP
    #[Js]
    public function showCount()
    {
        return "alert('Count is: {$this->count}')";
    }
};
```

```blade
<div>
    <button @click="$wire.showCount()">Show Count (from PHP)</button>
    <button @click="$js.incrementLocal()">Increment Local (from JS)</button>
</div>

@script
<script>
    // JavaScript defined in JavaScript
    $js('incrementLocal', () => {
        console.log('No server request made')
    })
</script>
@endscript
```

## Learn more

For more information about JavaScript integration in Livewire, see:

* [JavaScript documentation](/docs/4.x/javascript)
* [JavaScript actions documentation](/docs/4.x/actions#javascript-actions)
