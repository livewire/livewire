## Slots

Slots are an important part of any component templating system, however Livewire components don't support them currently.

Livewire components in v4 should match Blade component's slot API as closely as possible so there are the fewest surprises possible.

Let's briefly detail exactly how a user interacts with slots in Blade components.

## Slots in Blade components

**Passing a slot to a component:**

Here's how a user might pass a slot of text to a button component:

```php
<x-button>Create</x-button>
```

**Referencing the passed in slot:**

Now, here's the source of the button component that would echo the slot into the actual button element:

```php
// button.blade.php

<button type="button">{{ $slot }}</button>
```

**Passing named slots to a component:**

Sometimes the single slot is not enough and you want to pass additional, named, slots. Here's how a user might additionally pass in markup for an icon:

```php
<x-button>
    <x-slot name="icon"><svg>...</svg></x-slot>

    // Or the alternate shorter ":" syntax:

    <x-slot:icon><svg>...</svg></x-slot>

    Create
</x-button>
```

**Referencing the passed in slot and named slot:**

Named slots come through like normal props to a Blade component and can be echo'd into the template:

```php
// button.blade.php

@props([
    'icon' => null,
])

<button type="button">
    @if ($icon)
        <div class="icon">{{ $icon }}</div>
    @endif

    {{ $slot }}
</button>
```

**Passing in named slot attributes**

Additionally named slots can have their own attributes like ids, and classes and such:

```php
<x-button>
    <x-slot name="icon" class="mr-2">...</x-slot>

    Create
</x-button>
```

Then you access them via the `->attributes` property on the `$slot` object:

```php
// button.blade.php

@props([
    'icon' => null,
])

<button type="button">
    @if ($icon)
        <div {{ $icon->attributes->class('icon') }}>{{ $icon }}</div>
    @endif

    {{ $slot }}
</button>
```

**Accessing the component instance within a slot (like scoped slots)**

To provide an experience kinda like scoped slots in Vue or render props in React, Blade component slots allow you to access the $component instance from inside the slot like so:

```php
<x-button size="lg">
    <x-slot name="icon" class="mr-2">
        @if ($component->isLargeSize())
            ...
        @endif
    </x-slot>

    Create
</x-button>
```

This assumes of course that there is a `isLargeSize()` method on the Button.php Blade class-based component.

### Technical details

Slots passed into components start as strings, but are instantiated into Illuminate\View\ComponentSlot objects and passed into the component.

Here's a quick overview of the affordances available on that object:

```php

// Access an attribute bag set on the slot...
$slot->attributes;

// Set an array of attributes as an attribute bag on the slot instance...
$slot->withAttributes([...]);

// Determine if a slot is empty (because the $slot variable always exists but sometimes nothing was passed into it)...
$slot->isEmpty();

// Opposite of isEmpty()...
$slot->isNotEmpty();

// Determine if the slot contents have content besides whitespace and HTML comments...
$slot->hasActualContent();

// Return the raw string contents of the slot (So that it is compatible with a blade escaped echo like {{ $slot }})...
$slot->toHtml();
```

## Slots in v4 Livewire components

It would be FAIRLY trivial to support slots in Livewire components like they are supported in Blade.

Slots would function basically as props being passed in.

Users would store them as properties on their Livewire component class and echo them normally like they would in an anonymous component.

The problem with this setup however is that because Livewire component's are islands, if a parent component re-rendered and the slot it passed to a child changed, it wouldn't be reflected in the child component at all. Just like how props work. In theory a user could mark the prop in the child as `#[Reactive]` and the child snapshot would be sent back to the server with the parent request so that it could re-render with the updated data, however, this really isn't ideal.

But just for clarity, let me first lay out how this could work in v4:

### Solution A) Simple slot support

**Passing a slot to a component:**

Here's how a user might pass a slot of text to a modal component:

```php
<wire:modal>Create</wire:modal>
```

**Referencing the passed in slot:**

Now, here's the source of a theoretical modal Livewire component that would echo the slot as the contents of a modal:

```php
// modal.wire.php

@php
new class extends Livewire\Component {
    public $isOpen = false;

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
    }
}
@endphp

<div wire:show="isOpen">
    <button wire:click="toggle">Close</button>

    {{ $slot }}
</div>
```

**Passing named slots to a component:**

Here's how a user might uniquely configure the header content for a modal using named slots:

```php
<wire:modal>
    <wire:slot name="header">
        <h1>Create new post</h1>
    </wire:slot>

    <form>...</form>
</wire:modal>
```

**Referencing the passed in slot and named slot:**

Named slots will come through like normal props to a Blade component and can be echo'd into the template:

```php
// modal.wire.php

@php
new class extends Livewire\Component {
    public $header = null;

    public $isOpen = false;

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
    }
}
@endphp

<div wire:show="isOpen">
    <button wire:click="toggle">Close</button>

    @if ($header)
        {{ $header }}
    @endif

    {{ $slot }}
</div>
```

#### The problems with Solution A)

**The slot is not-reactive**

Consider the parent component re-renders and the `$saved` property is set to true to show a success message in the modal to the user:

```php
<wire:modal>
    <form>
        @if ($saved) The form has been submitted! @endif

        ...
    </form>
</wire:modal>
```

This change wouldn't be reflected in the modal on the page because in our naiive implementation, slots are just props and in Livewire props are not reactive and components are islands...

**Slots have to travel as state back and forth every request**

Because in this simple implementation, every time the child modal component re-renders, it has to send all the slot contents and attributes as part of the request snapshot which can be pretty heavy.

**Scope of things like wire:model**

Consider how a user might expect they can use wire:model to bind to a property in the parent from inside the modal slot like so:

```php
<wire:modal>
    <form>
        <input type="text" wire:model="title">

        ...
    </form>
</wire:modal>
```

However, because that template is rendered and passed into the modal component and echoed into the modal component template, `wire:model` will be attempting to bind to a `title` property on the modal component, instead of the parent component that the template is stored on.

This is unintuitive and will likely trip users up. Especially because if they echo in PHP into the slot, they CAN access state of the parent, so it's not intuitive that JS-level things like wire:model or wire:click are scoped to the child instead.

Because of these three hurdles, I think it's worth exploring an alternate solution that would solve these problems with a different set of tradeoffs.

### Solution B) The advanced (template hole) solution

An alternate solution would be to instead of treating slots like normal Livewire props, we treat them as something special: holes in the child component's template that get filled with parent slots. Then when the parent re-renders it would re-render the slots and send them to the browser and the browser would morph them in the slot holes of the child.

This would have a few restrictions that we can get to later (particularly with named slots).

Let's walk through it

**Passing a slot to a component:**

A user would pass a default slot like normal...

```php
<wire:modal>Create</wire:modal>
```

**Referencing the passed in slot:**

Now, when they consume it, it would FEEL like the normal `{{ $slot }}` object, and maybe it would be for the initial render, but at least on subsequent renders, `{{ $slot }}` would render a placeholder like: <slot></slot> or <template slot></template> or something, idk...

```php
// modal.wire.php

@php
new class extends Livewire\Component {
    public $isOpen = false;

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
    }
}
@endphp

<div wire:show="isOpen">
    <button wire:click="toggle">Close</button>

    {{ $slot }}
</div>
```

Then, if the child component refreshes, it will just re-render {{ $slot }} as a template element hole or something.

Then when Livewire is morphing the new HTML of the child component it will just skip the template part and use whatever is already there or something like that.

**Passing named slots to a component:**

Named slots are a little tricker. We could start with something like this:

```php
<wire:modal>
    <wire:slot name="header">
        <h1>Create new post</h1>
    </wire:slot>

    <form>...</form>
</wire:modal>
```

**Referencing the passed in slot and named slot:**

The tricky bit is when you try to reference them as normal props - we could make them normal props for the first render, but then subsequent ones, the content wouldn't be available (because it's just a hole) so if users wanted to do something like check the slot contents in some kind of logic it wouldn't work.

An alternative approach would be to use some kind of special slot syntax for Livewire like this:

```php
// modal.wire.php

@php
new class extends Livewire\Component {
    public $isOpen = false;

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
    }
}
@endphp

<div wire:show="isOpen">
    <button wire:click="toggle">Close</button>

    @if ($slots->has('header'))
        {{ $slots->get('header') }}
    @endif

    {{ $slot }}
</div>
```

As you can see, I introduced a new `$slots` variable that would be capable of rendering the slot holes.

## Recommended Path Forward: Solution B (Template Holes) - Default Slot Only

After thinking through approaches, **Solution B is the only viable path** that solves the fundamental reactivity and scoping problems.

### Proposed API

**Passing a default slot to a component:**

```php
<wire:modal>
    <form>
        <input wire:model="title">
        @if ($saved) Form saved successfully! @endif
    </form>
</wire:modal>
```

**Referencing the default slot in child component:**

```php
// modal.wire.php

@php
new class extends Livewire\Component {
    public $isOpen = false;

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
    }
}
@endphp

<div wire:show="isOpen">
    <button wire:click="toggle">Close</button>

    {{ $slot }}
</div>
```

**Passing a named slot to a component:**

```php
<wire:modal>
    <wire:slot name="header">
        <h1>Create post</h1>
    </wire:slot>

    <form>
        <input wire:model="title">
        @if ($saved) Form saved successfully! @endif
    </form>
</wire:modal>
```

**Referencing a:**

```php
// modal.wire.php

@php
new class extends Livewire\Component {
    public $isOpen = false;

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
    }
}
@endphp

<div wire:show="isOpen">
    @if ($header = $slot('header'))
        {{ $header }}
    @endif

    <button wire:click="toggle">Close</button>

    {{ $slot }}
</div>
```

### How It Works

#### Initial Render (Server-Side)
1. Parent component renders and encounters `<wire:modal>`
2. Livewire extracts the slot content: `<form>...</form>`
3. Child component renders normally, `{{ $slot }}` outputs the actual slot content wrapped in comment markers that denote the beginning and end of a slot so that we can track it in JS and not need a single root node. They should be like the islands or morph markers type format: `<!--[if SLOT:[slot-name][parent-component-id]]><![endif]-->...content...<!--[if ENDSLOT]><![endif]-->` (only imnclude parent component id if the parent is passed into ->withSlots()...)
4. Browser receives complete HTML with slot content in place wrapped in markers.
5. The child also has some new "memo" that is which slots were passed in - but just info about them like name and parent id

#### Child Re-renders (Islands Stay Islands)
1. User clicks "Close" button → child component re-renders independently
2. Child renders `{{ $slot() }}` as a placeholder slot: `<!--[if SLOT:[slot-name]:[parent-component-id]]><![endif]--><!--[if ENDSLOT]><![endif]-->`
3. Livewire morphs the child's new HTML but **skips the slot holes** using Alpine morph's "skipUntil" hook in updating hook:
```js
Alpine.morph(el, toHtml, {
    updating(from, to, childrenOnly, skip, skipChildren, skipUntil) {
        if (isStartCommentMarker(from) && isStartCommentMarker(to)) {
            skipUntil(node => isEndCommentMarker(node))
        }
    },
}))
```
do that in livewire's morph file probably - but maybe just add the skipUntil method to the morph.updating hook and use that in the supportSlots.js file
4. Slot content remains unchanged in the DOM

#### Parent Re-renders (Slot Content Updates)
1. Parent state changes (e.g., `$saved = true`)
2. Parent re-renders and instead of passing the slots as a variable to the child, the ->trackSlotForSubsequentUpdate or whatever method gets fired so that that dat acan make it's way into an effect in the payload that will then get processed in JS and morphed using "morphBetween" or whatever

The only thing here we haven't detailed is how scoping is handled.

We need to update $wire to detect look up the dom siblings to detect if it is inside a slot and if that slot has a parent-component-id and then maybe use that element for scope? that would probably do it yeah. so basically making it invisibly the $parent deal insetead $wire under the hood at the $wire level. (but im talking about the alpine $wire magic alpine variable to be clear.)

### Key Benefits

✅ **Reactive**: Parent changes automatically reflect in child slots
✅ **Correct Scoping**: `wire:model="title"` binds to parent component
✅ **Performance**: No heavy state transfer between parent/child
✅ **Island Architecture**: Each component remains independent
✅ **Familiar API**: Feels like Blade components
