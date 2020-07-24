<div>

    <div x-data="{ show: $wire.entangle('show') }">
        <button @click="show = ! show">toggle</button>

        <h1 x-show.transition="$wire.show">Model Contents</h1>
    </div>

    <!-- Counter -->
    <!-- <div x-data="{ count: 0 }">
        <span x-text="count" dusk="count.alpine"></span>
        <button @click="count++" dusk="increment.alpine">Inc (Alpine)</button>
        <span dusk="count.livewire">{{ $count }}</span>
        <button wire:click="increment" dusk="increment.livewire">Inc (Livewire)</button>
        <button wire:click="$refresh" dusk="refresh">Refresh</button>
    </div> -->

    <!-- $wire Livewire Counter -->
    <!-- <div x-data>
        <span x-text="$wire.count" dusk="count.wire"></span>
        <button @click="$wire.count++" dusk="increment1.wire">Inc1</button>
        <button @click="$wire.increment()" dusk="increment2.wire">Inc2</button>
    </div> -->

    <!-- $wire.entangle -->
    <!-- <div x-data="{ count: $wire.entangle('count') }">
        <span x-text="count" dusk="count.entangle"></span>
        <button @click="count++" dusk="increment.entangle">Inc (Entangle)</button>
    </div> -->

    <!-- let return = await $wire.method() -->
    <!-- <div x-data="{ value: null }" x-init="$wire.getCount().then(count => value = count)">
        <span x-text="value" dusk="count.method"></span>
        <button @click="value = await $wire.getCount()" dusk="refresh.method">Inc (Method Return)</button>
    </div> -->

    <!-- Transitions -->
    <!-- <div x-data="{ show:  }" x-init="$wire.getCount().then(count => value = count)">
        <span x-text="value" dusk="count.method"></span>
        <button @click="value = await $wire.getCount()" dusk="refresh.method">Inc (Method Return)</button>
    </div> -->

    <script src="http://alpine.test/dist/alpine.js" defer></script>
</div>
