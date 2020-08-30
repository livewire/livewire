<div>
    <div @if($count < 3) wire:poll.80ms @endif wire:key="1">
        <span dusk="output">{{ $count }}</span>
    </div>
</div>
