
Refs in Livewire provide a way to name, then target an individual element or component inside Livewire.

They're useful for dispatching events or streaming content to a specific element.

They're a tidy alternative, but conceptually similar, to using something like classes/ids to target elements.

Here are a list of use-cases:

- Dispatching an event to a specific component
- Targeting an element using `$refs`
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

        $this->dispatch('close')->to(ref: 'modal');
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

## Accessing DOM elements

When you add `wire:ref` to an HTML element, you can access it via the `$refs` magic property.

Consider a character counter that updates in real-time:

```php
<div>
    <textarea wire:model="message" wire:ref="message"></textarea>

    Characters: <span wire:ref="count">0</span>

    <!-- ... -->
</div>

<script>
    this.$refs.message.addEventListener('input', (e) => {
        this.$refs.count.textContent = e.target.value.length
    })
</script>
```

## Accessing `$wire`

If you wish to access `$wire` for a component with a ref, you can do so via the `.$wire` propert on the element:

```php
<div>
    <!-- ... -->

    <livewire:modal wire:ref="modal">
        <!-- ... -->

        <button wire:click="save()">Save</button>
    </livewire:modal>
</div>

<script>
    this.$intercept('save', ({ onFinish }) => {
        onFinish(() => {
            this.$refs.modal.$wire.close()
        })
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
            $this->stream($chunk)->to(ref: 'answer');
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

## Reference

```blade
wire:ref="name"
```

This directive has no modifiers.
