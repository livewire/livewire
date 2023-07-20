<div>
    <div x-data="{ items: @entangle('items').live }">
        <button @click="items.push('baz')" dusk="button">Add Baz</button>

        <div dusk="output.alpine">
            <h1>JavaScript List:</h1>
            <template x-for="item in items">
                <div x-text="item"></div>
            </template>
        </div>

        <div dusk="output.blade">
            <h1>Server rendered List:</h1>
            @foreach($items as $item)
                <div>{{ $item }}</div>
            @endforeach
        </div>
    </div>

    <div x-data>
        <button wire:click="$set('showBob', true)" dusk="bob.show">Show Bob</button>

        <div dusk="bob.blade">{{ $bob }}</div>

        <div>
            @if ($showBob)
                <div x-data="{ bob: @entangle('bob').live }">
                    <button x-on:click="bob = 'after'" dusk="bob.button">Change Bob</button>

                    <div dusk="bob.alpine" x-text="bob"></div>
                </div>
            @endif
        </div>
    </div>
</div>
