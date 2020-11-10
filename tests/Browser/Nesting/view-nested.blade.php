<div>
    <button wire:click="$set('output', 'foo')" dusk="button.nested"></button>

    <span dusk="output.nested">{{ $output }}</span>
</div>
