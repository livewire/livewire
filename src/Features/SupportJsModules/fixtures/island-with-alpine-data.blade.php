<?php

new class extends Livewire\Component {
    public bool $show = false;

    public function toggle()
    {
        $this->show = ! $this->show;
    }
};
?>

<div>
    @island
        <div dusk="placeholder">No child yet</div>

        @if ($show)
            <livewire:testns::alpine-data />
        @endif

        <button dusk="toggle" wire:click="toggle">toggle</button>
    @endisland
</div>
