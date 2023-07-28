
In a traditional HTML page containing a form, the form is only ever submitted when the user presses the "Submit" button.

However, Livewire is capable of much more than traditional form submissions. You can validate form inputs in real-time or even save the form as a user types.

In these "real-time" update scenarios, it can be helpful to signal to your users when a form or subset of a form has been changed, but hasn't been saved to the database.

When a form contains un-saved input, that form is considered "dirty". It only becomes "clean" when a network request has been triggered to synchronize the server state with the client-side state.

## Basic usage

Livewire allows you to easily toggle visual elements on the page using the `wire:dirty` directive.

By adding `wire:dirty` to an element, you are instructing Livewire to only show the element when the client-side state diverges from the server-side state.

To demonstrate, here is an example of an `UpdatePost` form containing a visual "Unsaved changes..." indication that signals to the user that the form contains input that has not been saved:

```blade
<form wire:submit="update">
    <input type="text" wire:model="title">

    <!-- ... -->

    <button type="submit">Update</button>

    <div wire:dirty>Unsaved changes...</div> <!-- [tl! highlight] -->
</form>
```

Because `wire:dirty` has been added to the "Unsaved changes..." message, the message will be hidden by default. Livewire will automatically display the message when the user starts modifying the form inputs.

When the user submits the form, the message will disappear again, since the server / client data is back in sync.

### Removing elements

By adding the `.remove` modifier to `wire:dirty`, you can instead show an element by default and only hide it when the component has "dirty" state:

```blade
<div wire:dirty.remove>The data is in-sync...</div>
```

## Targeting property updates

Imagine you are using `wire:model.blur` to update a property on the server immediately after a user leaves an input field. In this scenario, you can provide a "dirty" indication for only that property by adding `wire:target` to the element that contains the `wire:dirty` directive.

Here is an example of only showing a dirty indication when the title property has been changed:

```blade
<form wire:submit="update">
    <input wire:model.blur="title">

    <div wire:dirty wire:target="title">Unsaved title...</div> <!-- [tl! highlight] -->

    <button type="submit">Update</button>
</form>
```

## Toggling classes

Often, instead of toggling entire elements, you may want to toggle individual CSS classes on an input when its state is "dirty".

Below is an example where a user types into an input field and the border becomes yellow, indicating an "unsaved" state. Then, when the user tabs away from the field, the border is removed, indicating that the state has been saved on the server:

```blade
<input wire:model.blur="title" wire:dirty.class="border-yellow-500">
```

