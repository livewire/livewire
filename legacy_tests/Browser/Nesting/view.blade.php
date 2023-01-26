<div>
    <button wire:click="$toggle('showChild')" dusk="button.toggleChild"></button>

    <button wire:click="$set('key', 'bar')" dusk="button.changeKey"></button>

    @if ($showChild)
        @livewire('nested', key($key))
    @endif
</div>
