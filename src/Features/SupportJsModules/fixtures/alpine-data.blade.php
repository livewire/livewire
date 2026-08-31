<?php

new class extends Livewire\Component {
    //
};
?>

<div x-data="testAlpineData">
    <span dusk="target" x-text="message"></span>

    <button dusk="refresh" wire:click="$refresh">refresh</button>
</div>

<script>
    Alpine.data('testAlpineData', () => ({
        message: 'alpine-data-loaded',
    }))
</script>
