<div @if($count >= 4) wire:poll.90ms @endif wire:key="2">
    <button wire:click="$refresh" dusk="button">count++</button>
    <div @if($count < 3) wire:poll.80ms @endif wire:key="1">
        <span dusk="output">{{ $count }}</span>
    </div>
</div>
