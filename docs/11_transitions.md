# CSS Transitions

One of the benefits of using Livewire is utilizing familiar backend functionality like Blade, while making smooth front-ends. Livewire provides a simple CSS Transition system to help acheive this effect.

Livewire provides a basic "fade" transition out-of-the-box.

**view**
```html
<div>
    [...]

    @if($showConfirmationModal)
        <div wire:transition.fade>
            [...]
        </div>
    @endif
</div>
```

When `$showConfirmationModal` is `true`, it's contents are shown. When `$showConfirmationModal` becomes `false`, the contents will fade out, rather than dissapear instantly.

You can control the length of this fade by adding an additional time modifier. The following directive will cause the element to fade in and out for a duration of one second.

`wire:transition.fade.1s`

## Custom transitions

For custom transitions, Livewire borrows VueJs's transition syntax.

Let's say we want to add a "fade in and out" transition to a confirmation modal in our component. To achieve this, we need to first declare the transition in our view using Livewire's `wire:transition` directive.

**view**
```html
<div>
    [...]

    @if($showConfirmationModal)
        <div wire:transition="fade">
            [...]
        </div>
    @endif
</div>
```

Now, we need to provide the appropriate CSS selectors in our app's stylesheet for this transition:

```css
.fade-enter-active, .fade-leave-active {
  transition: all .2s ease;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
}
```

As you can see, Livewire applies the following four classes to the component you want to transition before adding or removing the element from the page:

Class | Description
--- | ---
.[transition]-enter | This gets added before the transition in
.[transition]-enter-active | This gets added one frame after the transition starts
.[transition]-leave-active | This gets added during the transition out
.[transition]-leave-to | This gets added one frame after the transition finishes
