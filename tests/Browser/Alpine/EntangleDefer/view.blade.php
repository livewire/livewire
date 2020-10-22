<div>
    <div x-data="{ alpineShow: @entangle('livewireShow').defer }">
        <div>
            <h1>Javascript show:</h1>
            <div dusk="output.alpine" x-text="alpineShow.toString()"></div>
        </div>

        <div>
            <h1>Server rendered show:</h1>
            <div dusk="output.livewire">{{ $livewireShow ? "true" : "false" }}</div>
        </div>

        <button dusk="toggle" x-on:click="alpineShow = ! alpineShow">Toggle Show</button>

        <button dusk="refresh" wire:click="$refresh">Refresh</button>
    </div>
</div>
