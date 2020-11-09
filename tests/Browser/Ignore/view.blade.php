<div>
    <button wire:click="$set('foo', true)" @if ($foo) some-new-attribute="true" @endif wire:ignore dusk="foo">Foo</button>

    <button wire:click="$set('bar', true)" wire:ignore dusk="bar">
         <span dusk="bar.output">{{ $bar ? 'new' : 'old' }}</span>
    </button>

    <button wire:click="$set('baz', true)" @if ($baz) some-new-attribute="true" @endif wire:ignore dusk="baz">Baz</button>
         <span dusk="baz.output">{{ $baz ? 'new' : 'old' }}</span>
    </button>

    <button wire:click="$set('bob', true)" wire:ignore dusk="bob">
         <span dusk="bob.output">{{ $bob ? 'new' : 'old' }}</span>
    </button>

    <button wire:click="$set('lob', true)" @if ($lob) some-new-attribute="true" @endif wire:ignore dusk="lob">lob</button>
         <span dusk="lob.output">{{ $lob ? 'new' : 'old' }}</span>
    </button>
</div>
