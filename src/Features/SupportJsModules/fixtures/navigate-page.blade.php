<?php

new class extends Livewire\Component {
    //
};
?>

<div x-data="navigateAlpineData">
    <div dusk="page-marker">page-{{ request()->query('page', 'one') }}</div>

    <span dusk="target" x-text="message"></span>

    <a href="/navigate-page?page=two" wire:navigate dusk="next-link">next</a>
</div>

<script>
    Alpine.data('navigateAlpineData', () => ({
        message: 'navigate-data-loaded',
    }))
</script>
