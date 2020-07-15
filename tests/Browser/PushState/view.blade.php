<div>
    <span dusk="output">{{ $foo }}</span>
    <span dusk="bar-output">{{ $bar }}</span>

    <input wire:model="foo" type="text" dusk="input">
    <input wire:model="bar" type="text" dusk="bar-input">

    <button wire:click="$set('showNestedComponent', true)" dusk="show-nested">Show Nested Component</button>

    @if ($showNestedComponent)
        @livewire(\Tests\Browser\PushState\NestedComponent::class)
    @endif
</div>
