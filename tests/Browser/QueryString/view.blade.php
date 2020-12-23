<div>
    <span dusk="output">{{ $foo }}</span>
    <span dusk="bar-output">{{ $bar }}</span>

    <input wire:model="foo" type="text" dusk="input">
    <input wire:model="bar" type="text" dusk="bar-input">

    <button wire:click="$set('showNestedComponent', true)" dusk="show-nested">Show Nested Component</button>

    <button wire:click="modifyBob" dusk="bob.modify">Modify Bob (Array Property)</button>
    <span dusk="bob.output">@json($bob)</span>

    @if ($showNestedComponent)
        @livewire(\Tests\Browser\QueryString\NestedComponent::class)
    @endif
</div>
