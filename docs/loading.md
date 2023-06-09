Loading indicators are an important part of crafting good user interfaces. They give users visual feedback when a request is being made to the server so they know they are waiting for a process to complete.

## Basic Usage

Livewire provides a simple yet extremely powerful syntax for controlling loading indicators: `wire:loading`. Adding `wire:loading` to any element will hide it by default (using `display: none`) and show it when a request is sent to the server.

Here's a basic example of a `CreatePost` component's form with `wire:loading` being used to toggle a loading message:

```html
<form wire:submit="save">
    <!-- ... -->

    <button type="submit">Save</button>

    <div wire:loading>
        Saving post...
    </div>
</form>
```

When a user presses "Save", the "Saving post..." message will appear below the button while the "save" action is being executed. The message will disappear when the response is received from the server and processed by Livewire.

### Removing elements

Alternatively, you can append `.remove` for the inverse effect, showing an element by default and hiding it during a server roundtrip.

```html
<div wire:loading.remove>...</div>
```

## Toggling classes

In addition to toggling entire elements, it's often useful to change the styling of an existing element by toggling CSS classes on and off. This technique can be used for things like changing background colors, lowering opacity, triggering spinning animations, etc.

Below is a simple example of using the [Tailwind](https://tailwindcss.com/) class `opacity-50` to make the "Save" button fainter while the form is being submitted.

```html
<button wire:loading.class="opacity-50">Save</button>
```

Like toggling an element, you can perform the inverse by using appending `.remove`:

```html
<button class="bg-blue-500" wire:loading.class.remove="bg-blue-500">
    Save
</button>
```

The above button's `bg-blue-500` class will be removed instead of added when the "Save" button is pressed.

## Toggling attributes

By default, when a form is submitted, Livewire will automatically disable the submit button while the form is being processed, as well as add the `readonly` attribute to each input element.

However, there may be other times when you want this behavior outside of a form submission or want to toggle other attributes on an element while "loading".

For these cases, Livewire provides a `.attr` modifier that works like `.class`, except it toggles HTML attributes instead of toggling classes on and off.

```html
<button
    type="button"
    wire:click="remove"
    wire:loading.attr="disabled"
>
    Remove
</button>
```

Because the above button isn't a submit button, it won't be disabled by Livewire when pressed. Instead, we added `wire:loading.attr="disabled"` to achieve this behavior.

## Targeting specific actions

By default, `wire:loading` will be triggered whenever a component makes a server roundtrip.

However, in components with multiple triggers for server roundtrips, you should scope your loading indicators down to individual actions.

For example, consider the following "Save post" form. In addition to a "Save" button that submits the form, there might also be a "Remove" button that executes a separate "remove" action in the component.

By adding `wire:target` to the following `wire:loading` element you can tell Livewire to only show  the loading message when the "Remove" button is clicked.

```html
<form wire:submit="save">
    <!-- ... -->

    <button type="submit">Save</button>

    <button type="button" wire:click="remove">Remove</button>

    <div wire:loading wire:target="remove">
        Removing post...
    </div>
</form>
```

When the above "Remove" button is pressed, the "Removing post..." message will show. When "Save" is pressed, the message will not show.

### Targeting action parameters

In cases where you have multiple of the same action trigger with different parameters like in the following example where each post has its own individual "Remove" button, you can further couple `wire:target` to a specific action by passing in extra parameters:

```html
<div>
    @foreach ($posts as $post)
        <div>
            <h2>{{ $post->title }}</h2>

            <button wire:click="remove({{ $post->id }})">Remove</button>

            <div wire:loading wire:target="remove({{ $post->id }})">
                Removing post...
            </div>
        </div>
    @endforeach
</div>
```

Without passing `{{ $post->id }}` to `wire:target="remove"`, the "Removing post..." message would show when any of the buttons on the page are clicked.

However, because we are passing in unique parameters to each instance of `wire:target`, Livewire will only show the loading message when the matching parameters are passed to the "remove" action.

### Targeting property updates

Livewire allows you to target specific component property updates and actions by passing in the property's name.

Take the following example where a form input called `username` uses `wire:model.live` for real-time validation as a user types.

```html
<form wire:submit="save">
    <input type="text" wire:model.live="username">

    <div wire:loading wire:target="username">
        Checking avilability of username...
    </div>

    <!-- ... -->
</form>
```

The "Checking availibility..." message will show when the server is updated with the new username as the user types into the input field.

## Customizing CSS display property

When `wire:loading` is added to an element, internally, Livewire updates the CSS `display` property of the element to show and hide the element.

By default, it uses `none` to hide and `inline-block` to show.

If you are toggling an element that uses a different display value, like `flex` in the following example, you can append `.flex` to `wire:loading`

```html
<div class="flex" wire:loading.flex>...</div>
```

Here is the complete list of available display values:

```html
<div wire:loading.inline-flex>...</div>
<div wire:loading.inline>...</div>
<div wire:loading.block>...</div>
<div wire:loading.table>...</div>
<div wire:loading.flex>...</div>
<div wire:loading.grid>...</div>
```

## Delaying a loading indicator

On fast connections, updates happen so quickly that loading indicators only flash briefly on the screen before being removed. In these cases, the indicator is more of a nuisance than a helpful affordance.

Livewire provides a `.delay` modifier to delay the showing of an indicator.

For example, if you add `wire:loading.delay` to an element like so:

```html
<div wire:loading.delay>...</div>
```

The above element will only appear if the request takes over 200 milliseconds. The user will never see the indicator if the request completes a roundtrip before then.

To customize the amount of time to delay the indicator, you can use one of Livewire's helpful shorthands:

```html
<div wire:loading.delay.shortest>...</div> <!-- 50ms -->
<div wire:loading.delay.shorter>...</div>  <!-- 100ms -->
<div wire:loading.delay.short>...</div>    <!-- 150ms -->
<div wire:loading.delay>...</div>          <!-- 200ms -->
<div wire:loading.delay.long>...</div>     <!-- 300ms -->
<div wire:loading.delay.longer>...</div>   <!-- 500ms -->
<div wire:loading.delay.longest>...</div>  <!-- 1000ms -->
```
