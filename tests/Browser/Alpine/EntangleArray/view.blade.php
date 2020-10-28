<div>
    <div x-data="{
        alpineList: @entangle('livewireList'),
        alpineSearch: @entangle('livewireSearch')
    }">
        <div>
            <h1>Javascript show:</h1>

            <div dusk="output.alpine">
                <ul>
                    <template x-for="item in alpineList">
                        <li x-text="item"></li>
                    </template>
                </ul>
            </div>
        </div>

        <div>
            <h1>Server rendered show:</h1>

            <div dusk="output.livewire">
                <ul>
                @foreach($livewireList as $item)
                    <li>{{ $item }}</li>
                @endforeach
                </ul>
            </div>
        </div>

        <input dusk="search" x-model="alpineSearch" />
        <button dusk="change" wire:click="change">Change List</button>
    </div>
</div>
