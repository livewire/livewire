<div>
    <button wire:click="$toggle('showChild')" dusk="button.toggleChild"></button>
    <button wire:click="$set('key', 'bar')" dusk="button.changeKey"></button>
    <input type="text" wire:model="search" dusk="input.search">

    @if ($showChild)
        @livewire(Tests\Browser\Nesting\NestedComponent::class, key($key))
    @endif

    @foreach ($items as $key => $item)
        <div>{{ $item }} @livewire(Tests\Browser\Nesting\ListedComponent::class, key($key))</div>
    @endforeach
</div>
