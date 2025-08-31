<div dusk="outer-external-island">
    <p>Outer external island content</p>

    <p dusk="outer-external-island-count">Outer external island count: {{ $this->count }}</p>

    <button wire:click="incrementCount" dusk="outer-external-island-count-button">Outer external island increment count</button>

    @island(view: 'islands::inner-external-island')
</div>