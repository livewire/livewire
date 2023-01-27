<div>
    <button wire:click="$refresh" dusk="refresh">count++</button>
    <button wire:click="$set('enabled', true)" dusk="enable">enable</button>
    <button wire:click="$set('enabled', false)" dusk="disable">disable</button>

    <span dusk="output">{{ $count }}</span>

    @if ($enabled) <div wire:poll.500ms></div> @endif
</div>
