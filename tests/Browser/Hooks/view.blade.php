<div>
    <button wire:click="showFoo" dusk="button">show</button>
    <input id="output-before" dusk="output.before" />
    <input id="output-after" dusk="output.after" />
    @if($foo)
        <div dusk="foo"></div>
    @endif
</div>

