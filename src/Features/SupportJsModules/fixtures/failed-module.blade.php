<?php

new class extends Livewire\Component {
    public $count = 0;
};
?>

<div x-data="{ ready: false }" x-init="ready = true">
    <span dusk="ready" x-text="ready ? 'ready' : 'waiting'">waiting</span>

    <button wire:click="$set('count', {{ $count + 1 }})" dusk="increment">Increment</button>
    <span dusk="count">{{ $count }}</span>
</div>

<script>
    import '/missing-component-module.js'
</script>
