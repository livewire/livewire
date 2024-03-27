
Loading indicators are an important part of crafting good user interfaces. They give users visual feedback when a request is being made to the server, so they know they are waiting for a process to complete.

## Basic usage

Livewire provides a simple yet extremely powerful syntax for controlling loading indicators: `wire:loading`. Adding `wire:loading` to any element will hide it by default (using `display: none` in CSS) and show it when a request is sent to the server.

Below is a basic example of a `CreatePost` component's form with `wire:loading` being used to toggle a loading message:

```blade
<form wire:submit="save">
    <!-- ... -->

    <button type="submit">Save</button>

    <div wire:loading> <!-- [tl! highlight:2] -->
        Saving post...
    </div>
</form>
```

When a user presses "Save", the "Saving post..." message will appear below the button while the "save" action is being executed. The message will disappear when the response is received from the server and processed by Livewire.

### Removing elements

Alternatively, you can append `.remove` for the inverse effect, showing an element by default and hiding it during requests to the server:

```blade
<div wire:loading.remove>...</div>
```

## Toggling classes

In addition to toggling the visibility of entire elements, it's often useful to change the styling of an existing element by toggling CSS classes on and off during requests to the server. This technique can be used for things like changing background colors, lowering opacity, triggering spinning animations, and more.

Below is a simple example of using the [Tailwind](https://tailwindcss.com/) class `opacity-50` to make the "Save" button fainter while the form is being submitted:

```blade
<button wire:loading.class="opacity-50">Save</button>
```

Like toggling an element, you can perform the inverse class operation by appending `.remove` to the `wire:loading` directive. In the example below, the button's `bg-blue-500` class will be removed when the "Save" button is pressed:

```blade
<button class="bg-blue-500" wire:loading.class.remove="bg-blue-500">
    Save
</button>
```

## Toggling attributes

By default, when a form is submitted, Livewire will automatically disable the submit button and add the `readonly` attribute to each input element while the form is being processed.

However, in addition to this default behavior, Livewire offers the `.attr` modifier to allow you to toggle other attributes on an element or toggle attributes on elements that are outside of forms:

```blade
<button
    type="button"
    wire:click="remove"
    wire:loading.attr="disabled"
>
    Remove
</button>
```

Because the button above isn't a submit button, it won't be disabled by Livewire's default form handling behavior when pressed. Instead, we manually added `wire:loading.attr="disabled"` to achieve this behavior.

## Targeting specific actions

By default, `wire:loading` will be triggered whenever a component makes a request to the server.

However, in components with multiple elements that can trigger server requests, you should scope your loading indicators down to individual actions.

For example, consider the following "Save post" form. In addition to a "Save" button that submits the form, there might also be a "Remove" button that executes a "remove" action on the component.

By adding `wire:target` to the following `wire:loading` element, you can instruct Livewire to only show the loading message when the "Remove" button is clicked:

```blade
<form wire:submit="save">
    <!-- ... -->

    <button type="submit">Save</button>

    <button type="button" wire:click="remove">Remove</button>

    <div wire:loading wire:target="remove">  <!-- [tl! highlight:2] -->
        Removing post...
    </div>
</form>
```

When the above "Remove" button is pressed, the "Removing post..." message will be displayed to the user. However, the message will not be displayed when the "Save" button is pressed.

### Targeting multiple actions

You may find yourself in a situation where you would like `wire:loading` to react to some, but not all, actions on a page. In these cases you can pass multiple actions into `wire:target` separated by a comma. For example:

```blade
<form wire:submit="save">
    <input type="text" wire:model.blur="title">

    <!-- ... -->

    <button type="submit">Save</button>

    <button type="button" wire:click="remove">Remove</button>

    <div wire:loading wire:target="save, remove">  <!-- [tl! highlight:2] -->
        Updating post...
    </div>
</form>
```

The loading indicator ("Updating post...") will now only be shown when the "Remove" or "Save" button are pressed, and not when the `$title` field is being sent to the server.

### Targeting action parameters

In situations where the same action is triggered with different parameters from multiple places on a page, you can further scope `wire:target` to a specific action by passing in additional parameters. For example, consider the following scenario where a "Remove" button exists for each post on the page:

```blade
<div>
    @foreach ($posts as $post)
        <div wire:key="{{ $post->id }}">
            <h2>{{ $post->title }}</h2>

            <button wire:click="remove({{ $post->id }})">Remove</button>

            <div wire:loading wire:target="remove({{ $post->id }})">  <!-- [tl! highlight:2] -->
                Removing post...
            </div>
        </div>
    @endforeach
</div>
```

Without passing `{{ $post->id }}` to `wire:target="remove"`, the "Removing post..." message would show when any of the buttons on the page are clicked.

However, because we are passing in unique parameters to each instance of `wire:target`, Livewire will only show the loading message when the matching parameters are passed to the "remove" action.

> [!warning] Multiple action parameters aren't supported
> At this time, Livewire only supports targeting a loading indicator by a single method/parameter pair. For example `wire:target="remove(1)"` is supported, however `wire:target="remove(1), add(1)"` is not.

### Targeting property updates

Livewire also allows you to target specific component property updates by passing the property's name to the `wire:target` directive.

Consider the following example where a form input named `username` uses `wire:model.live` for real-time validation as a user types:

```blade
<form wire:submit="save">
    <input type="text" wire:model.live="username">
    @error('username') <span>{{ $message }}</span> @enderror

    <div wire:loading wire:target="username"> <!-- [tl! highlight:2] -->
        Checking availability of username...
    </div>

    <!-- ... -->
</form>
```

The "Checking availability..." message will show when the server is updated with the new username as the user types into the input field.

### Excluding specific loading targets

Sometimes you may wish to display a loading indicator for every Livewire request _except_ a specific property or action. In these cases you can use the `wire:target.except` modifier like so:

```blade
<div wire:loading wire:target.except="download">...</div>
```

The above loading indicator will now be shown for every Livewire update request on the component _except_ the "download" action.

## Customizing CSS display property

When `wire:loading` is added to an element, Livewire updates the CSS `display` property of the element to show and hide the element. By default, Livewire uses `none` to hide and `inline-block` to show.

If you are toggling an element that uses a display value other than `inline-block`, like `flex` in the following example, you can append `.flex` to `wire:loading`:

```blade
<div class="flex" wire:loading.flex>...</div>
```

Below is the complete list of available display values:

```blade
<div wire:loading.inline-flex>...</div>
<div wire:loading.inline>...</div>
<div wire:loading.block>...</div>
<div wire:loading.table>...</div>
<div wire:loading.flex>...</div>
<div wire:loading.grid>...</div>
```

## Delaying a loading indicator

On fast connections, updates often happen so quickly that loading indicators only flash briefly on the screen before being removed. In these cases, the indicator is more of a distraction than a helpful affordance.

For this reason, Livewire provides a `.delay` modifier to delay the showing of an indicator. For example, if you add `wire:loading.delay` to an element like so:

```blade
<div wire:loading.delay>...</div>
```

The above element will only appear if the request takes over 200 milliseconds. The user will never see the indicator if the request completes before then.

To customize the amount of time to delay the loading indicator, you can use one of Livewire's helpful interval aliases:

```blade
<div wire:loading.delay.shortest>...</div> <!-- 50ms -->
<div wire:loading.delay.shorter>...</div>  <!-- 100ms -->
<div wire:loading.delay.short>...</div>    <!-- 150ms -->
<div wire:loading.delay>...</div>          <!-- 200ms -->
<div wire:loading.delay.long>...</div>     <!-- 300ms -->
<div wire:loading.delay.longer>...</div>   <!-- 500ms -->
<div wire:loading.delay.longest>...</div>  <!-- 1000ms -->
```
