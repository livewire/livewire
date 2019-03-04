# CSS Transitions

One of the benefits of using Livewire is utilizing familiar backend functionality like Blade, while making sophisticated, smooth front-ends. Livewire provides a simple CSS Transition system to help acheive this effect.

Again, Livewire borrows VueJs's transition syntax, so if you are familiar with Vue, this should be familiar to you.

Let's say we want to add a "fade in and out" transition to a confirmation modal in our component. To achieve this, we need to first, declare the transition in our view using Livewire's `wire:transition` directive.

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
.[transition]-enter | This get's added before the transition in
.[transition]-enter-active | This is added during the transition in
.[transition]-leave-active | This gets added add the beginning of the transition out
.[transition]-leave-to | This gets added after the transition is actively out
