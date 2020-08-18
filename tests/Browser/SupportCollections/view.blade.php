<div>
    <button wire:click="addBar" dusk="add-bar">Add Bar</button>

    @foreach ($things as $thing)
        <h1>{{ $thing }}</h1>
    @endforeach
</div>
