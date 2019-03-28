# Data Binding
If you've used front-end frameworks like Angular, React, or Vue, you are already familiar with this concept. However, if you are new to this concept, allow me to domonstrate.

Consider the following Livewire component:

<div title="Component"><div title="Component__class">

FavoriteColor
```php
class FavoriteColor extends LivewireComponent
{
    public $color;

    public function render()
    {
        return view('livewire.favorite-color');
    }
}
```
</div>
<div title="Component__view">

favorite-color.blade.php
```html
<div>
    What's your favorite color?

    <select wire:model="color">
        <option>Pink</option>
        <option>Purple</option>
        <option>Yellow</option>
    </select>

    Your favorite color is {{ $color }}
</div>
```
</div>
</div>

When the user selects a favorite color from the dropdown, the value of the `$color` property will automatically update. Livewire knows to keep track of the selected color because of the `wire:model` directive.

Internally, Livewire listens for "input" events on the element and updates the class property with the element's value. Therefore, you can apply `wire:model` to any element that emits `input` events.

Common elements to use `wire:model` on include:

Element Tag |
--- |
`<input>` |
`<select>` |
`<textarea>` |

<div title="Warning"><div title="Warning__content">

Be careful using `wire:model` on `<input>` elements, it is usually better to use `wire:model.lazy` instead. See below for more info.
</div></div>

## Lazily Updating

By default, Livewire sends a request to server after every "input" event. This is usually fine for things like `<select>` elements that don't update frequently, however, this is often unnescessary for text fields that update as the user types.

In those cases, use the `lazy` directive modifier to save on network requests.

<div title="Component"><div title="Component__class">

Todos
```php
class Todos extends LivewireComponent
{
    public $todo;

    public function render()
    {
        return view('livewire.todos');
    }
}
```
</div><div title="Component__view">

favorite-color.blade.php
```html
<div>
    <input wire:model.lazy="todo">
    <button wire:click="addTodo">Add Todo</button>
</div>
```
</div></div>

Now, the `$todo` property will only be updated when the user clicks the "Add Todo" button.
