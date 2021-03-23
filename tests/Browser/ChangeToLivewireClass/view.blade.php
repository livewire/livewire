<div>
    <p>$aProp: {{ $aProp ?? '(unset)' }}</p>
    <p>$zProp: {{ $zProp ?? '(unset)' }}</p>
    <p>$count: {{ $count }}</p>
    <button wire:click="incCount" dusk="inc-count">inc counter</button>
</div>
