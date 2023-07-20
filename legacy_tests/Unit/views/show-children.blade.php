<div>
    @foreach ($children as $child)
        @livewire('child', ['name' => $child], key($child))
    @endforeach
</div>
