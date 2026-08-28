
In a traditional HTML page containing a form, the form is only ever submitted when the user presses the "Submit" button.

However, Livewire is capable of much more than traditional form submissions. You can validate form inputs in real-time or even save the form as a user types.

In these "real-time" update scenarios, it can be helpful to signal when a form or subset of a form has changed but hasn't reached the server.

When the browser contains input that differs from the server state, that input is considered "dirty". It becomes "clean" when a message synchronizes the browser and server state.

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

Imagine you are using `wire:model.live.blur` to update a property on the server immediately after a user leaves an input field. In this scenario, you can provide a "dirty" indication for only that property by adding `wire:target` to the element that contains the `wire:dirty` directive.

Here is an example of only showing a dirty indication when the title property has been changed:

```blade
<form wire:submit="update">
    <input wire:model.live.blur="title">

    <div wire:dirty wire:target="title">Unsaved title...</div> <!-- [tl! highlight] -->

    <button type="submit">Update</button>
</form>
```

## Toggling classes

Often, instead of toggling entire elements, you may want to toggle individual CSS classes on an input when its state is "dirty".

Below is an example where a user types into an input field and the border becomes yellow, indicating an "unsaved" state. Then, when the user tabs away from the field, the border is removed, indicating that the state has been saved on the server:

```blade
<input wire:model.live.blur="title" wire:dirty.class="border-yellow-500">
```

## Using the `$dirty` expression

In addition to the `wire:dirty` directive, you can check dirty state programmatically using the `$dirty` expression in Livewire directives or `$wire.$dirty()` in Alpine.

### Check if entire component is dirty

To check if any property on the component has unsaved changes:

```blade
<div wire:show="$dirty">You have unsaved changes</div>
```

### Check if a specific property is dirty

To check if a specific property has been modified:

```blade
<div wire:show="$dirty('title')">Title has been modified</div>
```

You can also check nested properties:

```blade
<div wire:show="$dirty('user.name')">Name has been modified</div>
```

### Conditional logic based on dirty state

You can use `$wire.$dirty()` in Alpine to conditionally run logic:

```blade
<button x-on:click="$wire.$dirty('title') && $wire.save()">
    Save Title
</button>
```

Or apply conditional classes with Alpine:

```blade
<input
    wire:model="email"
    :class="$wire.$dirty('email') && 'border-yellow-500'"
>
```

## Persisting dirty state across messages

By default, dirty state describes whether the server has received the browser's latest changes. Any message can therefore make the component clean, even when that message didn't save anything to a database.

For an unsaved-changes indicator, add the `.persist` modifier and mark the state as clean after it has been saved:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Component;
use App\Models\Post;

new class extends Component {
    public $title = '';

    public function save()
    {
        Post::create($this->only(['title']));

        $this->markAsClean(); // [tl! highlight]
    }
};
?>

<form wire:submit="save">
    <input type="text" wire:model="title">

    <button type="submit">Save</button>

    <div wire:dirty.persist>Unsaved changes...</div> <!-- [tl! highlight] -->
</form>
```

Persistent dirty state survives polling, live model updates, and unrelated actions. Clean server-side changes are accepted into its baseline automatically, while local edits remain dirty until `markAsClean()` runs.

The `$dirty` expression accepts the same option:

```blade
<div x-show="$wire.$dirty('title', { persist: true })">Unsaved title...</div>
```

From Alpine, `$wire.$markAsClean()` marks the current browser state as clean without sending a message:

```blade
<button type="button" x-on:click="$wire.$markAsClean()">Accept changes</button>
```

## Reference

```blade
wire:dirty
wire:target="property"
```

### Modifiers

| Modifier | Description |
|----------|-------------|
| `.remove` | Show element by default, hide when dirty |
| `.class="class-name"` | Add a CSS class when dirty |
| `.persist` | Compare against the last state marked as clean instead of the latest server state |

### `$dirty` expression

| Expression | Description |
|------------|-------------|
| `$dirty` | Returns `true` if any property has unsaved changes |
| `$dirty('property')` | Returns `true` if the specified property has unsaved changes |
| `$dirty(['title', 'description'])` | Returns `true` if any of the specified properties have unsaved changes |
| `$dirty('title', { persist: true })` | Compares the property against the last state marked as clean |

Can be used in Livewire directives like `wire:show="$dirty"` or in Alpine as `$wire.$dirty()`.

### Marking state as clean

| Call | Description |
|------|-------------|
| `$this->markAsClean()` | Marks the state returned by the current message as clean |
| `$wire.$markAsClean()` | Marks the current browser state as clean |
