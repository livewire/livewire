<div>
    <button wire:click="$toggle('showChild')" dusk="button.toggleChild"></button>

    <button wire:click="$set('key', 'bar')" dusk="button.changeKey"></button>

    @if ($showChild)
        @livewire(Tests\Browser\Nesting\NestedComponent::class, key($key))
    @endif
</div>
