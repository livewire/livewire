<div id="nested-root-a">
    <span dusk="lastEventForChildA">{{ $this->lastEvent }}</span>
    <span dusk="lastBarEventA">{{ $lastBarEvent }}</span>

    <button wire:click="$emitOthers('bar', 'others: received')" dusk="emit.fireBarToOthers"></button>
</div>
