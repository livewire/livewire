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

After thinking through both approaches, **Solution B is the only viable path** that solves the fundamental reactivity and scoping problems.

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

### How It Works

#### Compile Pass
1. Compiler encounters `<wire:modal>` and starts output buffers to collect slots
2. Compiler encounters `</wire:modal>` and `ob_get_clean()` gets output buffer content and puts them into a $slots variable that gets passed into: `@livewire('name', $props, $slots, $key)` or something

#### Initial Render (Server-Side)
1. Parent component encounters `@livewire` directive with slots passed in (not actually @livewire, but the underlying compiled php you know? something like `app('livewire')->mount(...)`)
2. `$component->withSlots($slots)` gets called to set the slots onto the child component
3. When the child component renders, a `$slot` variable is made available (instantiated with the slots that are set to the slots property - probably a key value array of slot names and content?)
3. Child component renders normally, `{{ $slot }}` outputs the actual slot content because of a toHtml() on the slot object
4. Browser receives complete HTML with slot content in place

#### Child Re-renders (Islands Stay Islands)
1. User clicks "Close" button → child component re-renders independently
2. Child renders `{{ $slot }}` as a **template hole**: `<template data-wire-slot="default"></template>`
3. Livewire morphs the child's new HTML but **skips the slot holes**
4. Slot content remains unchanged in the DOM

#### Parent Re-renders (Slot Content Updates)
1. Parent state changes (e.g., `$saved = true`)
2. Parent re-renders and updates slot content
3. Livewire sends **slot content updates** to browser
4. Browser updates content inside the slot holes: `<template data-livewire-slot="default">`

### Things to figure out

#### Does something need to be injected onto the root element of a slot on first-render so that on future morphing the "from" and "to" elements can be checked for matching data-wire-slot="..." attributes and skipped?

#### How will scope for wire:* directives be leap-frogged up the DOM/component tree so that slot content access the scope of the parent its defined on?

#### Slots are generally expected to not need to have a root element, so this makes adding an attribute like that tough. We could use comment markers at the beginning and end, but then what about the scope leap-frogging that needs to be done, as livewire directives are just alpine directives under the hood and alpine directives get their state by looking up the dom tree for closest element that provides scope.

#### When a parent makes a subsequent request, where does the child slots go? is it in the effects payload as a key called slots? and an ogbject of key/value pairs for deafult/named slots?

#### Do we have to store information about passed in slots onto the child dcomponent after first render so that when the child-rerenders it has an idea of which slots it theoretically supports like what their names are and some qualities about those slots. maybe things like attributes passed into them? although I think we can punt on that for not, but maybe things like, if they are empty or have actual slot content?

## Implementation Plan

Based on analysis of the partials feature and Livewire's existing patterns, here's the complete implementation plan for slots:

### Directory Structure
```
src/v4/Slots/
├── HandlesSlots.php          // Trait for component
├── SupportSlots.php          // Feature class with provide() render() dehydrate() type hooks
├── Slot.php                  // An individual, HTMLable slot
└── BrowserTest.php           // Browser tests

```

### 1. Precompilation & Tag Processing

**Modify WireTagPrecompiler** to detect and extract slot content:
- Hook into existing tag compilation to capture `<wire:slot name="header">` syntax
- Compile down to `@wireSlot` / `@endWireSlot` directives
- Register Blade directives to extract slot content using output buffers (similar to Laravel, but instead of calling $env->startSlot() and such, store them directly in a variable to be passed into @liveire(...))
- Store extracted slots in `$slots` variable for passing to `@livewire` directive

**Example compilation:**
```php
// Input:
<wire:modal>
    <wire:slot name="header">
        <h1>{{ $title }}</h1>
    </wire:slot>

    <form>...</form>
</wire:modal>

// Compiled output:
@wireSlot('default') <form>...</form> @endWireSlot
@wireSlot('header') <h1>{{ $title }}</h1> @endWireSlot
@livewire('modal', [...$props], $key, $__slots ?? [])
```

### 2. Blade Directive Registration

**In IntegrateV4.php**, register slot directives:
```php
Blade::directive('wireSlot', function ($expression) {
    return "<?php
        ob_start();
        \$__slotName = {$expression};
        \$__previousSlotName = \$__slotName ?? null;

        // Track slot stack for nesting support
        \$__slotStack = \$__slotStack ?? [];
        array_push(\$__slotStack, \$__previousSlotName);
    ?>";
});

Blade::directive('endWireSlot', function () {
    return "<?php
        \$__slotContent = ob_get_clean();
        \$__slots = \$__slots ?? [];
        \$__slots[\$__slotName] = \$__slotContent;

        // Store on parent component for subsequent render tracking
        if (isset(\$_instance) && \$_instance instanceof \Livewire\Component) {
            \$_instance->trackSlotForSubsequentRenders(\$__slotName, \$__slotContent);
        }

        // Restore previous slot name from stack for nesting
        \$__slotName = array_pop(\$__slotStack);
    ?>";
});
```

**Why This Works:**
- **Immediate Use**: Slots stored in `$__slots` for passing to `@livewire` call
- **Persistent Tracking**: Also stored on parent component via `trackSlotForSubsequentRenders()`
- **Nested Support**: Stack-based approach handles nested slots properly
- **Reactive Content**: Re-captures updated content on each parent render

### 3. Component Mounting & Slot Injection

**Modify component mounting** to accept slots parameter:
```php
// In HandleComponents.php mount() method:
public function mount($name, $params = [], $key = null, $slots = [])
{
    // ... existing code ...

    if (! empty($slots)) {
        $component->withSlots($slots, $parent);
    }

    // ... rest of mounting logic ...
}
```

**Add withSlots() and slot tracking methods** to Component base class:
```php
// In Component.php or HandlesSlots trait:
protected $slots = [];
protected $trackedSlots = [];

public function withSlots(array $slots, $parent = null): self
{
    $parentId = $parent?->getId();

    $this->slots = collect($slots)->map(function ($content, $name) use ($parentId) {
        return new Slot($name, $content, $parentId);
    });

    return $this;
}

// Critical for subsequent render tracking
public function trackSlotForSubsequentRenders(string $name, string $content): void
{
    $this->trackedSlots[$name] = $content;
}

public function getTrackedSlots(): array
{
    return $this->trackedSlots;
}

public function hasSlots(): bool
{
    return !empty($this->trackedSlots);
}

public function getSlotObjectForView()
{
    return new class ($this->slots) {
        public function __construct(protected $slots) {}

        public function __invoke($name = 'default')
        {
            return $this->get($name);
        }

        public function get($name)
        {
            return $this->slots[$name] ?? $this->throwSlotNotFoundError($name);
        }

        public function nullPatternSlot($name)
        {
            throw new \Exception('No slot found by the name of: ' . $name);
        }

        public function toHtml()
        {
            return $this->get('default');
        }
    };
}
```

### 4. Slot Object Implementation

**Create Slot.php:**
```php
class Slot implements Htmlable, Stringable
{
    public function __construct(
        public string $name,
        public string $content,
        public string $parentComponentId,
        public bool $isEmpty = false
    ) {}

    public function toHtml(): string
    {
        // On first render, return content with slot markers
        if ($this->isFirstRender()) {
            return $this->wrapWithSlotMarkers($this->content);
        }

        // On subsequent renders, return placeholder
        return $this->getSlotPlaceholder();
    }

    private function wrapWithSlotMarkers(string $content): string
    {
        return "<!--[SLOT:{$this->name}:{$this->parentComponentId}]-->"
             . $content
             . "<!--[/SLOT:{$this->name}]-->";
    }

    private function getSlotPlaceholder(): string
    {
        return "<!--[SLOT:{$this->name}:{$this->parentComponentId}]-->"
             . "<!--[/SLOT:{$this->name}]-->";
    }

    public function isEmpty(): bool
    {
        return empty(trim(strip_tags($this->content)));
    }
}
```

### 5. View Variable Injection

**Hook into component rendering** to provide `$slot` and `$slots` variables:
```php
// In SupportSlots.php
public function render($view, $data)
{
    $view->with([
        'slot' => $this->component->getSlotObjectForView();
    ])

    ...
}
```

### 6. Effects Handling (Following Partials Pattern)

**Server-side effects generation:**
```php
// In SupportSlots dehydrate method:
public function dehydrate($context)
{
    if ($context->isMounting()) return;

    // Use tracked slots from blade directive execution
    $trackedSlots = $this->component->getTrackedSlots();
    if (empty($trackedSlots)) return;

    // Find child components that need slot updates
    $childrenWithSlots = $this->findChildrenThatAcceptSlots();

    $slotsEffects = [];
    foreach ($childrenWithSlots as $childId) {
        $slotsEffects[$childId] = $trackedSlots;
    }

    $context->addEffect('slots', $slotsEffects);
}

private function findChildrenThatAcceptSlots(): array
{
    // Look at component memo to find which child components have slots
    // This would be populated during initial mount when slots are passed
    return $this->component->memo['childrenWithSlots'] ?? [];
}
```

**Slot Tracking Flow:**
1. **First Render**: Parent executes `@wireSlot` directives → content captured in `$__slots` for child + stored via `trackSlotForSubsequentRenders()`
2. **Subsequent Renders**: Parent re-renders → `@wireSlot` directives execute again → new content stored via `trackSlotForSubsequentRenders()`
3. **Effects Generation**: `dehydrate()` accesses `getTrackedSlots()` → sends updated content to children
4. **Client Updates**: JavaScript morphs slot content in child components

**Client-side effects handling:**
```javascript
// js/features/supportSlots.js (copy from supportPartials.js)
on('effect', ({ component, effects }) => {
    let slots = effects.slots?.[component.id]
    if (!slots) return

    Object.entries(slots).forEach(([slotName, content]) => {
        queueMicrotask(() => {
            queueMicrotask(() => {
                let { startNode, endNode } = findSlotComments(component.el, slotName)
                if (!startNode || !endNode) return

                let strippedContent = stripSlotComments(content, slotName)
                Alpine.morphBetween(startNode, endNode, createContainer(strippedContent), getMorphConfig(component))
            })
        })
    })
})
```

### 7. Scope Handling Enhancement

**Modify closestComponent function** in js/store.js:
```javascript
export function closestComponent(el, strict = true) {
    // Check for slot parent scope first
    let slotParentId = findSlotParentId(el);
    if (slotParentId) {
        let parentEl = document.querySelector(`[wire\\:id="${slotParentId}"]`);
        if (parentEl?.__livewire) return parentEl.__livewire;
    }

    // Fall back to normal component lookup
    let closestRoot = Alpine.findClosest(el, i => i.__livewire);
    // ... existing logic
}

function findSlotParentId(el) {
    let current = el;
    while (current) {
        if (current.nodeType === Node.COMMENT_NODE) {
            let match = current.textContent.match(/\[SLOT:.*:([\w-]+)\]/);
            if (match) return match[1];
        }
        current = current.previousSibling ||
                 (current.parentNode !== el.getRootNode() ? current.parentNode : null);
    }
    return null;
}
```

### 8. Slot Metadata Storage

**Store slot metadata in component memo:**
```php
// During component mount/dehydrate:
$context->addMemo('slots', [
    'names' => array_keys($this->slots->toArray()),
    'hasContent' => $this->slots->mapWithKeys(fn($slot, $name) => [
        $name => !$slot->isEmpty()
    ])->toArray(),
]);
```

### 10. Testing Strategy

**Browser Tests:**
- Basic slot rendering and reactivity
- Named slots functionality
- Scope handling (wire:model, wire:click work in slots)
- Morphing behavior during parent/child updates

This implementation leverages the proven partials pattern while adding the scope-handling enhancements needed for slots to work seamlessly with Livewire's component island architecture.