
When a Livewire component update's the browser's DOM, it does so in an intelligent way we call "morphing".

The term _morph_ is in contrast with a word like _replace_.

Instead of _replacing_ a component's HTML with newly rendered HTML every time a component is updated, Livewire dynamically compares the current HTML with the new HTML, identifies differences, and makes surgical changes to the HTML only in the places where changes are needed.

This has the benefit of preserving existing, un-changed elements, on a component. For example, event listeners, focus state, and form input values are all preserved between Livewire updates. Not to mention the performance benefits of replacing small portions of the DOM rather than wiping and re-rending new DOM on every update.

## How morphing works

To understand how Livewire determines which elements to update between Livewire requests, consider this simple `Todos` component:

```php
class Todos extends Component
{
    public $todo = '';

    public $todos = [
        'first',
        'second',
    ];

    public function add()
    {
        $this->todos[] = $this->todo;
    }
}
```

```blade
<div>
    <ul>
        @foreach ($todos as $todo)
            <li>{{ $todo }}</li>
        @endforeach
    </ul>

    <input wire:model="todo" wire:keydown.enter="add">
</div>
```

The initial render of this component will output the following HTML:

```html
<form wire:submit="add">
    <ul>
        <li>first</li>

        <li>second</li>
    </ul>

    <input wire:model="todo">
</form>
```

Now, imagine you typed "third" into the input field and pressed the `[Enter]` key. The newly rendered HTML would be:

```html
<div>
    <ul>
        <li>first</li>

        <li>second</li>

        <li>third</li>
    </ul>

    <input wire:model="todo" wire:keydown.enter="add">
</div>
```

When Livewire process the component update, it _morphs_ the original DOM into the newly rendered HTML. The following visualization should intuitively give you an understanding of how it works:

<video width="100%" src="/visualizations/morph_basic.m4v" type="video/mp4" controls></video>

As you can see, Livewire walks both HTML trees simultaneously. As it encounters each element in both trees, it compares them for changes, additions, and removals. If it detects one, it surgically makes the appropriate change.

## Morphing shortcomings

The following are scenarios where morphing algorithms fail to correctly identify the change in HTML trees and therefore cause problems in your application.

### Inserting intermediate elements

Consider the following Livewire Blade template for a fictitous `CreatePost` component:

```blade
<form wire:submit="save">
    <div>
        <input type="text" wire:model="title">
    </div>

    @if (@error('title'))
        <div>Error: {{ $message }}</div>
    @endif

    <div>
        <button type="submit">Save</button>
    </div>
<div>
```

If a user tries submitting the form, but encounters a validation error, the following problem occurs:

// Visualization

As you can see, when Livewire encounters the new `<div>` for the error message, it doesn't know weather to change the existing `<div>` in-place, or insert the new `<div>` in the middle.

To re-iterate what's happening more explicitly:

* Livewire encounters the first `<div>` in both trees. They are the same, so it continues.
* Livewire encounters the second `<div>` in both trees and thinks they are the same `<div>`, just one has changed contents. So instead of inserting the error message as a new element, it changes the `<button>` into an error message.
* Livewire then, after mistakenly modifying the previous elemjent, notices an additional element at the end of the comparison. It then creates and appends the element after the previous one.
* Therefore destroying, then re-creating an element that otherwise should have been simply moved.

This scenario is at the root of almost all morph-related bugs.

Here are a few specific problematic impacts of these bugs:
* Event listeners and element state are lost between updates
* Event listeners and state are misplaced across the wrong elements
* Entire Livewire components can be reset or duplicated as Livewire components are also simply elements in the DOM tree
* Alpine components and state can be lost or misplaced

Fortunately, Livewire has worked hard to mitigate these problems using the following approaches:

#### Injecting morph markers

On the backend, Livewire automatically detects conditional inside Blade templates and wraps them in markers that Livewire's JavaScript can use as a guide when morphing.

Here's an example of the previous Blade template but with Livewire's injected markers:

```blade
<form wire:submit="save">
    <div>
        <input type="text" wire:model="title">
    </div>

    <!-- __BLOCK__ --> <!-- [tl! highlight] -->
    @if (@error('title'))
        <div>Error: {{ $message }}</div>
    @endif
    <!-- ENDBLOCK --> <!-- [tl! highlight] -->

    <div>
        <button type="submit">Save</button>
    </div>
<div>
```

With these markers injected into the template, Livewire can now more easily detect the difference between a change and an addition.

This feature is extremely beneficial to Livewire applications, but because it requires parsing templates via regex, it can sometimes fail to properly detect conditionals. If this feature is more of a hinderance than a help to your application, you can disable it with the following configuration in `config/livewire.php`:

```php
'inject_morph_markers' => false,
```

#### Internal look-ahead

In addition to injected markers, Livewire has an additional step in it's algorithm that checks subsequent elements and their contents before assuming an element should be changed rather than added.

Here is a visualization of the "look-ahead" algorithm in action:

// Visualization

#### Wrapping conditionals

If the above two solutions don't cover your situation, the most reliable way to avoid morphing problems is to wrap conditionals and loops in their own elements that are always present.

For example, here's the above Blade template rewritten with wrapping `<div>`s:

```blade
<form wire:submit="save">
    <div>
        <input type="text" wire:model="title">
    </div>

    <div> <!-- [tl! highlight] -->
        @if (@error('title'))
            <div>Error: {{ $message }}</div>
        @endif
    </div> <!-- [tl! highlight] -->

    <div>
        <button type="submit">Save</button>
    </div>
<div>
```

Now that the conditional has been wrapped in a persistant element, Livewire will morph the two different HTML trees properly.

Here's a visualization to better demonstrate:

// Visualization


