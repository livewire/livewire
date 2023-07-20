<div>
    <h1 dusk="page.title">{{ $title }}</h1>

    <div x-data="{
        active: @entangle('active')
    }">
        <div dusk="output.alpine" x-text="active"></div>
        <div dusk="output.livewire">{{ $active ? 'true' : 'false' }}</div>
        <button dusk="toggle" x-on:click="active = !active">Toggle Active</button>
    </div>
</div>
