<div>
    @foreach ($children as $child)
        @livewire('child', $child, key($child))
    @endforeach
</div>
