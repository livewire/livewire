
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

## Respecting reduced motion

Livewire automatically respects the user's `prefers-reduced-motion` setting. When enabled, transitions are disabled to avoid causing discomfort for users who are sensitive to motion.

## Browser support

View Transitions are supported in Chrome 111+, Edge 111+, and Safari 18+. In browsers that don't support View Transitions, elements will appear and disappear without animation—the functionality still works, just without the visual transition.

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
