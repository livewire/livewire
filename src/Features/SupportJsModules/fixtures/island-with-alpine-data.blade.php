<?php

new class extends Livewire\Component {
    public $show = false;
};
?>

<div>
    @island(name: 'content')
        @if ($show)
            <livewire:testns::alpine-data.index />
        @else
            <div dusk="placeholder">No child yet</div>
        @endif
    @endisland

    <button wire:click="$toggle('show')" wire:island="content" dusk="toggle">Toggle</button>
</div>
