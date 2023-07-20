<div>
    <span wire:offline dusk="whileOffline">Offline</span>
    <span wire:offline.class="foo" dusk="addClass"></span>
    <span class="hidden" wire:offline.class.remove="hidden" dusk="removeClass"></span>
    <span wire:offline.attr="disabled" dusk="withAttribute"></span>
    <span wire:offline.attr.remove="disabled" disabled="true" dusk="withoutAttribute"></span>
</div>
