<div>
    <button wire:click="$set('output', 'foo')" dusk="foo">foo</button>

    <span dusk="output">{{ $output }}</span>
</div>
