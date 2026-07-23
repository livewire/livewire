<?php

new class extends Livewire\Component {
    //
};
?>

<div x-data="testAlpineData">
    <div dusk="target" x-text="message"></div>
</div>

<script>
    Alpine.data('testAlpineData', () => ({
        message: 'alpine-data-loaded',
    }))
</script>
