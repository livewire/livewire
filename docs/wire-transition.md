
`wire:transition` enables smooth animations when elements appear, disappear, or change using the browser's native [View Transitions API](https://developer.mozilla.org/en-US/docs/Web/API/View_Transitions_API).

Unlike JavaScript-based animation libraries, View Transitions are hardware-accelerated and handled natively by the browser, resulting in smoother animations with less overhead.

## Basic usage

Add `wire:transition` to any element that may be added, removed, or changed during a Livewire update:

```php
class ShowPost extends Component
{
    public Post $post;

    public $showComments = false;
}
```

```blade
<div>
    <button wire:click="$toggle('showComments')">Toggle comments</button>

    @if ($showComments)
        <div wire:transition> <!-- [tl! highlight] -->
            @foreach ($post->comments as $comment)
                <div>{{ $comment->body }}</div>
            @endforeach
        </div>
    @endif
</div>
```

When the comments appear or disappear, the browser will smoothly crossfade them instead of abruptly showing or hiding them.

## Named transitions

By default, Livewire assigns the view-transition-name `match-element` to elements with `wire:transition`. You can provide a custom name to enable more advanced transition effects:

```blade
<div wire:transition="sidebar">...</div>
```

This sets the element's `view-transition-name` CSS property to `sidebar`, which you can target with CSS for custom animations.

## Customizing animations with CSS

View Transitions are controlled entirely through CSS. You can customize the animation by targeting the view-transition pseudo-elements:

```css
/* Customize the transition for a specific element */
::view-transition-old(sidebar) {
    animation: 300ms ease-out slide-out;
}

::view-transition-new(sidebar) {
    animation: 300ms ease-in slide-in;
}

@keyframes slide-out {
    to { transform: translateX(-100%); }
}

@keyframes slide-in {
    from { transform: translateX(100%); }
}
```

The View Transitions API provides three pseudo-elements you can style:
- `::view-transition-old(name)` — The outgoing element's snapshot
- `::view-transition-new(name)` — The incoming element's snapshot
- `::view-transition-group(name)` — Container for both snapshots

## Transition types

For more complex scenarios like step wizards where you need different animations based on direction, you can use transition types. This allows you to animate "forward" and "backward" differently.

Use the `$this->transition()` method to set a transition type:

```php
class Wizard extends Component
{
    public $step = 1;

    public function goToStep($step)
    {
        $this->transition(type: $step > $this->step ? 'forward' : 'backward');

        $this->step = $step;
    }
}
```

Then target the type in your CSS using the `:active-view-transition-type()` selector:

```css
html:active-view-transition-type(forward) {
    &::view-transition-old(content) {
        animation: 300ms ease-out slide-out-left;
    }
    &::view-transition-new(content) {
        animation: 300ms ease-in slide-in-right;
    }
}

html:active-view-transition-type(backward) {
    &::view-transition-old(content) {
        animation: 300ms ease-out slide-out-right;
    }
    &::view-transition-new(content) {
        animation: 300ms ease-in slide-in-left;
    }
}

@keyframes slide-out-left {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(-100%); opacity: 0; }
}

@keyframes slide-in-right {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slide-out-right {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

@keyframes slide-in-left {
    from { transform: translateX(-100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
```

For methods that always transition in the same direction, you can use the `#[Transition]` attribute instead:

```php
use Livewire\Attributes\Transition;

class Wizard extends Component
{
    public $step = 1;

    #[Transition(type: 'forward')]
    public function next()
    {
        $this->step++;
    }

    #[Transition(type: 'backward')]
    public function previous()
    {
        $this->step--;
    }
}
```

## Skipping transitions

Sometimes you may want to disable transitions for specific actions—for example, a "reset" button that should instantly jump to the first step without animation.

Use `$this->skipTransition()` to disable transitions for the current request:

```php
public function reset()
{
    $this->skipTransition();

    $this->step = 1;
}
```

Or use the `#[Transition]` attribute with `skip: true`:

```php
use Livewire\Attributes\Transition;

#[Transition(skip: true)]
public function reset()
{
    $this->step = 1;
}
```

## Respecting reduced motion

Livewire automatically respects the user's `prefers-reduced-motion` setting. When enabled, transitions are disabled to avoid causing discomfort for users who are sensitive to motion.

## Browser support

View Transitions are supported in Chrome 111+, Edge 111+, and Safari 18+. In browsers that don't support View Transitions, elements will appear and disappear without animation—the functionality still works, just without the visual transition.

> [!warning] Firefox has limited support
> Firefox 128+ supports basic view transitions, but does not support transition types (`forward`/`backward`). If you use transition types, Firefox will fall back to the default crossfade animation.

[View browser support on caniuse.com →](https://caniuse.com/view-transitions)

## See also

- **[wire:show](/docs/4.x/wire-show)** — Toggle visibility with CSS display
- **[Loading States](/docs/4.x/loading-states)** — Show loading indicators during requests
- **[Alpine Transitions](https://alpinejs.dev/directives/transition)** — For more complex animation needs

## Reference

```blade
wire:transition="name"
```

| Expression | Description |
|------------|-------------|
| (none) | Uses `match-element` as the view-transition-name |
| `"name"` | Uses the provided string as the view-transition-name |

This directive has no modifiers.
