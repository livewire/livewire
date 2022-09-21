<div @if($enabled) wire:poll.500ms @endif>
    <button wire:click="$refresh" dusk="refresh">count++</button>
    <button wire:click="$set('enabled', true)" dusk="enable">enable</button>
    <button wire:click="$set('enabled', false)" dusk="disable">disable</button>

    <span dusk="output">{{ $count }}</span>
</div>
