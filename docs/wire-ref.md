
Refs in Livewire provide a way to name, then target an individual element or component inside Livewire.

They're useful for dispatching events or streaming content to a specific element.

They're a tidy alternative, but conceptually similar, to using something like classes/ids to target elements.

Here are a list of use-cases:

- Dispatching an event on a specific element
- Targeting a Livewire component using `$refs`
- Accessing DOM elements within a component from JavaScript
- Streaming content to a specific element

Let's walk through each of these.

## Dispatching events

Refs are a great way to target specific child components within Livewire's event system.

Consider the following Livewire modal component that listens for a _close_ event:

```php
<?php

new class extends Livewire\Component {
    public bool $isOpen = false;

    // ...

    #[On('close')]
    public function close()
    {
        $this->isOpen = false;
    }
};
?>

<div wire:show="isOpen">
    {{ $slot }}
</div>
```

By adding `wire:ref` to the component tag, you now dispatch the _close_ event directly to it using the `ref:` parameter:

```php
<?php

new class extends Livewire\Component {
    public function save()
    {
        //

        $this->dispatch('close', ref: 'modal');
    }
};
?>

<div>
    <!-- ... -->

    <livewire:modal wire:ref="modal">
        <!-- ... -->

        <button wire:click="save">Save</button>
    </livewire:modal>
</div>
```

## Using `$refs`

Similar to Livewire's `$parent` magic, the `$refs` magic allows you target another component within common directives like `wire:click`:

```php
<div>
    <!-- ... -->

    <livewire:modal wire:ref="modal">
        <button wire:click="$refs.modal.close()">Close</button>

        <!-- ... -->
    </livewire:modal>
</div>
```

Additionally, `$refs` is accessible directly from the `this` or `$wire` JavaScript context.

Consider an example that aims to handle the closing of the modal purely in JavaScript:

```php
<div>
    <!-- ... -->

    <livewire:modal wire:ref="modal">
        <!-- ... -->

        <button wire:click="save()">Save</button>
    </livewire:modal>
</div>

<script>
    this.intercept('save', ({ request }) => {
        request.onSuccess(() => {
            this.$refs.modal.close()
        })
    })
</script>
```

## Accessing DOM elements

When you add `wire:ref` to a plain HTML element, you can access the underlying DOM element using the `.el` property. This is useful for direct DOM manipulation without triggering a full component re-render.

Consider a character counter that updates in real-time:

```php
<div>
    <textarea wire:model="message" wire:ref="message"></textarea>

    Characters: <span wire:ref="count">0</span>

    <!-- ... -->
</div>

<script>
    this.$refs['message'].el.addEventListener('input', (e) => {
        this.$refs['count'].el.textContent = e.target.value.length`
    })
</script>
```

## Streaming content

Livewire supports streaming content directly to elements within a component using CSS selectors, however, `wire:ref` is a more convenient and discoverable approach.

Consider the following component that streams an answer directly from an LLM as it's generated:

```php
<?php

new class extends Livewire\Component {
    public $question = '';

    public function ask()
    {
        Ai::ask($this->question, function ($chunk) {
            $this->stream($chunk, ref: 'answer');
        });

        $this->reset('question');
    }
};
?>

<div>
    <input type="text" wire:model="question">

    <button wire:click="ask"></button>

    <h2>Answer:</h2>

    <p wire:ref="answer"></p>
</div>
```

## Naming conventions

Refs use kebab-case in HTML, matching Livewire's attribute naming style:

```php
<livewire:modal wire:ref="user-modal"></livewire>
```

In JavaScript, you can access refs using either camelCase or bracket notation:

```php
<script>
    // Both of these work...

    this.$refs.userModal.close()
    this.$refs['user-modal'].close()
</script>
```

## Dynamic refs

Refs work perfectly in loops and other dynamic contexts.

Here's an example with multiple modal instances:

```php
@foreach($users as $index => $user)
    <livewire:modal
        wire:key="{{ $user->id }}"
        wire:ref="{{ 'user-modal-' . $user->id }}"
    >
        <!-- ... -->
    </livewire>
@endforeach
```

## Scoping behavior

Refs are scoped to the current component. This means you can target any element within the component, but not elements in other components on the page.

If multiple elements have the same ref name within a component, the first one encountered will be used.
