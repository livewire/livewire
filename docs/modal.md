Modals or "popovers" are a common UI component used in web applications. Livewire provides one out-of-the-box for you.

To use the modal and other UI components, you can install `livewire/ui` using composer:

```php
composer require livewire/ui
```

Now from anywhere in your Blade templates you can use the `<wire:modal>` component:

```blade
<x-modal>
    <x-slot:title>
        Modal content here!
    </x-slot>

    <x-slot:body>
        Modal content here!
    </x-slot>
</x-modal>
```

## Toggling the modal via Livewire

```php
class ShowPost extends Component
{
    public $showConfirmationModal = false;

    public function remove()
    {
        if ($this->showConfirmationModal) {
            $this->post->remove();

            $this->showConfirmationModal = false;
        } else {
            $this->showConfirmationModal = true;
        }
    }
}
```

```blade
<div>
    <button wire:click="remove">Remove Post</button>

    <x-modal wire:model="showConfirmationModal">
        <x-slot:title>
            Confirm?
        </x-slot>

        <x-slot:body>
            Are you sure you want to remove the post?
        </x-slot>

        <x-slot:body>
            <button wire:click="$modal.close()">Remove</button>
            <button wire:click="remove">Remove</button>
        </x-slot>
    </x-modal>
</div>
```

## Toggling the modal via Alpine

```blade
<div x-data="{ showConfirmation: false }">
    <button x-on:click="showConfirmation = true">Remove Post</button>

    <x-modal x-model="showConfirmation">
        <x-slot:title>
            Confirm?
        </x-slot>

        <x-slot:body>
            Are you sure you want to remove the post?
        </x-slot>

        <x-slot:body>
            <button wire:click="$modal.close()">Cancel</button>
            <button wire:click="remove">Remove</button>
        </x-slot>
    </x-modal>
</div>
```

## Toggling via events

```blade
<div x-data="{ open: false }" x-on:close-modal.window="open = false">
    <x-modal x-model="open">
        <x-slot:title>
            Confirm?
        </x-slot>

        <x-slot:body>
            Are you sure you want to remove the post?
        </x-slot>

        <x-slot:body>
            <button wire:click="$modal.close()">Cancel</button>
        </x-slot>
    </x-modal>
</div>
```
