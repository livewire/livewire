<div>
    <button type="button" wire:click="setOutputToA" dusk="setA">setA</button>
    <button type="button" wire:click="setOutputToB" dusk="setB">setB</button>
    <span dusk="output">{{ $output }}</span>
</div>
