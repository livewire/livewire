<?php

use Livewire\Attributes\Lazy;

new #[Lazy] class extends Livewire\Component {
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
