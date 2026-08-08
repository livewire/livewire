<?php

use Livewire\Attributes\Lazy;

new #[Lazy] class extends Livewire\Component {
    //
};
?>

<div>
    <livewire:testns::lazy-with-alpine-data-in-slot.wrapper>
        <div dusk="target" x-data="testAlpineData" x-text="message"></div>
    </livewire:testns::lazy-with-alpine-data-in-slot.wrapper>
</div>

<script>
    Alpine.data('testAlpineData', () => ({
        message: 'alpine-data-loaded',
    }))
</script>
