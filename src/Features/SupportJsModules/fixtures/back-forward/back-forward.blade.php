<div>
    <div dusk="loaded">waiting</div>
    <div dusk="target">waiting</div>

    <button type="button" wire:click="$js.mark" dusk="mark">Mark</button>
    <button type="button" wire:click="$js.markAgain" dusk="mark-again">Mark again</button>

    <a href="/livewire-dusk/testns::back-forward-second" wire:navigate dusk="link">Go to second page</a>
</div>
