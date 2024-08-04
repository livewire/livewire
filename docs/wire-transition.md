
## Basic usage

Showing or hiding content in Livewire is as simple as using one of Blade's conditional directives like `@if`. To enhance this experience for your users, Livewire provides a `wire:transition` directive that allows you to transition conditional elements smoothly in and out of the page.

For example, below is a `ShowPost` component with the ability to toggle viewing comments on and off:

```php
use App\Models\Post;

class ShowPost extends Component
{
    public Post $post;

    public $showComments = false;
}
```

```blade
<div>
    <!-- ... -->

    <button wire:click="$set('showComments', true)">Show comments</button>

    @if ($showComments)
        <div wire:transition> <!-- [tl! highlight] -->
            @foreach ($post->comments as $comment)
                <!-- ... -->
            @endforeach
        </div>
    @endif
</div>
```
Because `wire:transition` has been added to the `<div>` containing the post's comments, when the "Show comments" button is pressed, `$showComments` will be set to `true` and the comments will "fade" onto the page instead of abruptly appearing.

## Limitations

Currently, `wire:transition` is only supported on a single element inside a Blade conditional like `@if`. It will not work as expected when used in a list of sibling elements. For example, the following will NOT work properly:

```blade
<!-- Warning: The following is code that will not work properly -->
<ul>
    @foreach ($post->comments as $comment)
        <li wire:transition wire:key="{{ $comment->id }}">{{ $comment->content }}</li>
    @endforeach
</ul>
```

If one of the above comment `<li>` elements were to get removed, you would expect Livewire to transition it out. However, because of hurdles with Livewire's underlying "morph" mechanism, this will not be the case. There is currently no way to transition dynamic lists in Livewire using `wire:transition`.

## Default transition style

By default, Livewire applies both an opacity and a scale CSS transition to elements with `wire:transition`. Here's a visual preview:

<div x-data="{ show: false }" x-cloak class="border border-gray-700 rounded-xl p-6 w-full flex justify-between">
    <a href="#" x-on:click.prevent="show = ! show" class="py-2.5 outline-none">
        Preview transition <span x-text="show ? 'out' : 'in →'">in</span>
    </a>
    <div class="hey">
        <div
            x-show="show"
            x-transition
            class="inline-flex px-16 py-2.5 rounded-[10px] bg-pink-400 text-white uppercase font-medium transition focus-visible:outline-none focus-visible:!ring-1 focus-visible:!ring-white"
            style="
                background: linear-gradient(109.48deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.1) 100%), #EE5D99;
                box-shadow: inset 0px -1px 0px rgba(0, 0, 0, 0.5), inset 0px 1px 0px rgba(255, 255, 255, 0.1);
            "
        >
            &nbsp;
        </div>
    </div>
</div>

The above transition uses the following values for transitioning by default:

Transition in | Transition out
--- | ---
`duration: 150ms` | `duration: 75ms`
`opacity: [0 - 100]` | `opacity: [100 - 0]`
`transform: scale([0.95 - 1])` | `transform: scale([1 - 0.95])`

## Customizing transitions

To customize the CSS Livewire internally uses when transitioning, you can use any combination of the available modifiers:

Modifier | Description
--- | ---
`.in` | Only transition the element "in"
`.out` | Only transition the element "out"
`.duration.[?]ms` | Customize the transition duration in milliseconds
`.duration.[?]s` | Customize the transition duration in seconds
`.delay.[?]ms` | Customize the transition delay in milliseconds
`.delay.[?]s` | Customize the transition delay in seconds
`.opacity` | Only apply the opacity transition
`.scale` | Only apply the scale transition
`.origin.[top\|bottom\|left\|right]` | Customize the scale "origin" used

Below is a list of various transition combinations that may help to better visualize these customizations:

**Fade-only transition**

By default, Livewire both fades and scales the element when transitioning. You can disable scaling and only fade by adding the `.opacity` modifier. This is useful for things like transitioning a full-page overlay, where adding a scale doesn't make sense.

```html
<div wire:transition.opacity>
```

<div x-data="{ show: false }" x-cloak class="border border-gray-700 rounded-xl p-6 w-full flex justify-between">
    <a href="#" x-on:click.prevent="show = ! show" class="py-2.5 outline-none">
        Preview transition <span x-text="show ? 'out' : 'in →'">in</span>
    </a>
    <div class="hey">
        <div
            x-show="show"
            x-transition.opacity
            class="inline-flex px-16 py-2.5 rounded-[10px] bg-pink-400 text-white uppercase font-medium transition focus-visible:outline-none focus-visible:!ring-1 focus-visible:!ring-white"
            style="
                background: linear-gradient(109.48deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.1) 100%), #EE5D99;
                box-shadow: inset 0px -1px 0px rgba(0, 0, 0, 0.5), inset 0px 1px 0px rgba(255, 255, 255, 0.1);
            "
        >
            ...
        </div>
    </div>
</div>

**Fade-out transition**

A common transition technique is to show an element immediately when transitioning in, and fade its opacity when transitioning out. You'll notice this effect on most native MacOS dropdowns and menus. Therefore it's commonly applied on the web to dropdowns, popovers, and menus.

```html
<div wire:transition.out.opacity.duration.200ms>
```

<div x-data="{ show: false }" x-cloak class="border border-gray-700 rounded-xl p-6 w-full flex justify-between">
    <a href="#" x-on:click.prevent="show = ! show" class="py-2.5 outline-none">
        Preview transition <span x-text="show ? 'out' : 'in →'">in</span>
    </a>
    <div class="hey">
        <div
            x-show="show"
            x-transition.out.opacity.duration.200ms
            class="inline-flex px-16 py-2.5 rounded-[10px] bg-pink-400 text-white uppercase font-medium transition focus-visible:outline-none focus-visible:!ring-1 focus-visible:!ring-white"
            style="
                background: linear-gradient(109.48deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.1) 100%), #EE5D99;
                box-shadow: inset 0px -1px 0px rgba(0, 0, 0, 0.5), inset 0px 1px 0px rgba(255, 255, 255, 0.1);
            "
        >
            ...
        </div>
    </div>
</div>

**Origin-top transition**

When using Livewire to transition an element such as a dropdown menu, it makes sense to scale in from the top of the menu as the origin, rather than center (Livewire's default). This way the menu feels visually anchored to the element that triggered it.

```html
<div wire:transition.scale.origin.top>
```

<div x-data="{ show: false }" x-cloak class="border border-gray-700 rounded-xl p-6 w-full flex justify-between">
    <a href="#" x-on:click.prevent="show = ! show" class="py-2.5 outline-none">
        Preview transition <span x-text="show ? 'out' : 'in →'">in</span>
    </a>
    <div class="hey">
        <div
            x-show="show"
            x-transition.origin.top
            class="inline-flex px-16 py-2.5 rounded-[10px] bg-pink-400 text-white uppercase font-medium transition focus-visible:outline-none focus-visible:!ring-1 focus-visible:!ring-white"
            style="
                background: linear-gradient(109.48deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.1) 100%), #EE5D99;
                box-shadow: inset 0px -1px 0px rgba(0, 0, 0, 0.5), inset 0px 1px 0px rgba(255, 255, 255, 0.1);
            "
        >
            ...
        </div>
    </div>
</div>

> [!tip] Livewire uses Alpine transitions behind the scenes
> When using `wire:transition` on an element, Livewire is internally applying Alpine's `x-transition` directive. Therefore you can use most if not all syntaxes you would normally use with `x-transition`. Check out [Alpine's transition documentation](https://alpinejs.dev/directives/transition) for all its capabilities.

