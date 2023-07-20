<div>
    {{-- <div x-data="{ count: $queryString(1) }">
        <input type="text" x-model="count">
        <span x-text="count"></span>
    </div>

    <br>
    <br>
    <br> --}}

    <span dusk="output">{{ $foo }}</span>
    <span dusk="bar-output">{{ $bar }}</span>

    <span dusk="qux.hyphen">{{ $qux['hyphen'] }}</span>
    <span dusk="qux.comma">{{ $qux['comma'] }}</span>
    <span dusk="qux.ampersand">{{ $qux['ampersand'] }}</span>
    <span dusk="qux.space">{{ $qux['space'] }}</span>
    <span dusk="qux.array">{{ json_encode($qux['array']) }}</span>

    <input wire:model.live="foo" type="text" dusk="input">
    <input wire:model.live="bar" type="text" dusk="bar-input">

    <button wire:click="$set('showNestedComponent', true)" dusk="show-nested">Show Nested Component</button>

    <button wire:click="modifyBob" dusk="bob.modify">Modify Bob (Array Property)</button>
    <span dusk="bob.output">@json($bob)</span>

    @if ($showNestedComponent)
        @livewire('nested')
    @endif
</div>
