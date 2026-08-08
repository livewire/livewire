<?php

use Livewire\Attributes\Lazy;

new #[Lazy] class extends Livewire\Component {
    //
};
?>

@placeholder
    <div>
        <livewire:testns::lazy-slot-with-existing-wrapper.wrapper>
            <div dusk="target">Loading...</div>
        </livewire:testns::lazy-slot-with-existing-wrapper.wrapper>
    </div>
@endplaceholder

<div>
    <livewire:testns::lazy-slot-with-existing-wrapper.wrapper>
        <div dusk="target" x-data="testSlotAlpineData" x-text="message"></div>
    </livewire:testns::lazy-slot-with-existing-wrapper.wrapper>
</div>

<script>
    import { greeting } from '/slow-slot-module.js'

    Alpine.data('testSlotAlpineData', () => ({
        message: greeting,
    }))
</script>
