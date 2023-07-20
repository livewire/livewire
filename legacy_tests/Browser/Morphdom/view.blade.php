<div @if ($foo) foo="true" @endif dusk="root">
    <button wire:click="$set('foo', true)" dusk="foo"></button>

    <button wire:click="$set('bar', true)" dusk="bar">
        <div dusk="bar.start">start</div>
        @if ($bar)
            <div dusk="bar.middle">middle</div>
        @endif
        <div dusk="bar.end">end</div>
    </button>

    <button wire:click="$set('baz', true)" dusk="baz">
        @if ($baz)
            <div>second</div>
        @endif
        <div>first</div>
    </button>

    <button wire:click="$set('bob', true)" dusk="bob">
        @if ($bob)
            <div>0</div>
        @endif

        <div wire:key="bob">1</div>

        <div>
            <div id="bob-id">2</div>
        </div>
    </button>

    <button wire:click="$set('qux', true)" dusk="qux">
        @if ($qux)
            <div>first</div>
        @endif

        <div>
            <div>second</div>
            <div wire:key="qux" data-qux="{{ $qux ? 'true' : 'false' }}">third</div>
        </div>
    </button>
</div>
