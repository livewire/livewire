<div>
    <button wire:click="showFoo" dusk="button">show</button>
    <input dusk="output" />

    @if($foo)
        <div dusk="foo"></div>
    @endif
</div>
