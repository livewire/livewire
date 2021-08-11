<div>
    <div x-data="{ button1: null, button2: null }">
        <button x-on:click="button1 = await $wire.get()" x-text="button1" dusk="button1"></button>

        <button x-on:click="button2 = await $wire.get()" x-text="button2" dusk="button2"></button>
    </div>
</div>
