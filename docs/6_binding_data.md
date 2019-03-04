# Data Binding
If you've used front-end frameworks like Angular, React, or Vue, you are already familiar with this concept. However, if you are new to this concept, it's fancy name my intimidate you. Fear not, it's actually quite straightforward.

Consider the following Livewire component:

```php
class FavoriteColor extends LivewireComponent
{
    public $color;

    public function render()
    {
        return view('livewire.favorite-color')->with('color', $this->color);
    }
}
```

**favorite-color.blade.php**
```html
<div>
    <input wire:model="color">
    <h1>My favorite color is: {{ $color }}</h1>
</div>
```

Anytime the value of the `<input>` element is updated, the class property `$this->color` in `FavoriteColor.php` will be automatically updated, and the new value will be passed down to view and re-rendered.

If this isn't clicking, trying going through the [Quickstart Guide](docs/quickstart.md). Seeing it work in real life may fix that.

Also, if you want to know how this magic works under the hood, check out [Under The Hood](docs/under_the_hood.md)

## Lazy updating

You can add the `wire:model` directive to any element that dispatches `input` events (usually this means input elements). This in mind, this can mean a lot of round-trips to the server and back while a user is typing into an input element. If you don't need the component property to update in real-time, only before you perform some action in Livewire, you can use the `.lazy` modifier.

For example, if we add a `.lazy` modifier to the `model` directive, we can cut down on requests:
```html
<div>
    <input wire:model.lazy="todo">
    <button wire:click="addTodo">Add Todo</button>
</div>
```

Now the value of `$this->todo` in the Livewire component will only be updated when the user clicks the "Add Todo" button, instead of the value updating every time the user types into the input.
