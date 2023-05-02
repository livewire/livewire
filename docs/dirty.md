
In a traditional HTML form setup, the form is only ever submitted when the user presses the "Submit" button.

However, Livewire is capable of much more than a traditional form submission. You can validate form inputs in real-time, or save the form as a user types.

In these "real-time" update scenarios, is can be helpful to signal to your users when a form or subset of a form has been changed, but hasn't been saved to the database.

When a form contains un-saved input, that form is considered "dirty". It only becomes "clean", when a network request has been triggered to synchronize the server state with the client-side state.

## Basic usage

Livewire allows you to easily toggle visual elements on the page using the `wire:dirty` syntax.

By adding `wire:dirty` to an element, you are telling Livewire to hide it anytime the client-side state is in-sync with the server-side state.

To demonstrate, below is an example of an `UpdatePost` form containing an "Unsaved changes..." visual indication that signals to the user that the form contains input that is "un-saved":

```html
<form wire:submit="update">
    <!-- ... -->

    <button type="submit">Update</button>

    <div wire:dirty>Unsaved changes...</div>
</form>
```

Because `wire:dirty` has been added to the "Unsaved changes..." message, initially the message will not show up, but as soon as a user starts modifying form inputs, it will show up.

When the form is submitted by the user, the message will dissapear as the server/client data is back in-sync.

### Removing elements

By adding `.remove`, you can instead show an element by default and only hide it when the component has "dirty" state.

```html
<div wire:dirty.remove>Changes are in-sync...</div>
```

## Targeting property updates

If you are using `wire:model.lazy` to update a property on the server after a user leaves the input field, you can provide a "dirty" indication for that specific property by adding `wire:target` to the element.

Here is an example of only showing a dirty indication when the title property has been changed

```html
<form wire:submit="update">
    <input wire:model.lazy="title">

    <div wire:dirty wire:target="title">Unsaved title...</div>

    <button type="submit">Update</button>
</form>
```

## Toggling classes

Often, instead of toggling entire elements, you may want to toggle individual CSS classes on an input when it's state is "dirty".

Here's an example where as a user types into an input field, the border becomes yellow, indicating an "unsaved" state, then when the user tabs away from the field, the border is removed, indicating that the state has been saved on the server:

```html
<input wire:model.lazy="title" wire:dirty.class="border-yellow-500">
```

