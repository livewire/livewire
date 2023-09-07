
## Basic usage

Showing or hiding content in Livewire is as simple as using one of Blade's conditional directives like `@if`. To enhance this experience for your users, Liveiwre provides a `wire:transition` directive that allows you to transition conditional elements smoothly in and out of the page.

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

## Default transition style

By default, Livewire applies both an opacity and a scale CSS transition to elements with `wire:transtion`. Here's a visual preview:

<div x-data="{ show: false }" class="border border-gray-700 rounded-xl p-6 w-full flex justify-between">
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
            ...
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
`.duration.[?]ms` | Customize the transtion duration in milliseconds
`.duration.[?]s` | Customize the transtion duration in seconds
`.delay.[?]ms` | Customize the transtion delay in milliseconds
`.delay.[?]s` | Customize the transtion delay in seconds
`.opacity` | Only apply the opacity transition
`.scale` | Only apply the scale transition
`.origin.[top|bottom|left|right]` | Customize the scale "origin" used

Below is a list of various transition combinations that may help to better visualize these customizations:

**Fade-only transition**

```html
<div wire:transition.opacity>
```

<div x-data="{ show: false }" class="border border-gray-700 rounded-xl p-6 w-full flex justify-between">
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

**Scale-only transition**

```html
<div wire:transition.scale>
```

<div x-data="{ show: false }" class="border border-gray-700 rounded-xl p-6 w-full flex justify-between">
    <a href="#" x-on:click.prevent="show = ! show" class="py-2.5 outline-none">
        Preview transition <span x-text="show ? 'out' : 'in →'">in</span>
    </a>
    <div class="hey">
        <div
            x-show="show"
            x-transition.scale
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

```html
<div wire:transition.out.opacity.duration.200ms>
```

<div x-data="{ show: false }" class="border border-gray-700 rounded-xl p-6 w-full flex justify-between">
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

```html
<div wire:transition.scale.origin.top>
```

<div x-data="{ show: false }" class="border border-gray-700 rounded-xl p-6 w-full flex justify-between">
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

