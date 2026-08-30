<?php

new class extends Livewire\Component {
    //
};
?>

<div x-data="lazyProbe">
    <span data-real dusk="probe-target" x-text="message"></span>
</div>

<script>
    window.lazyProbeResults = window.lazyProbeResults || []

    // Records whether this component's REAL markup (not a lazy placeholder)
    // was in the DOM when its script ran...
    window.lazyProbeResults.push(!! $wire.$el.querySelector('[data-real]'))

    Alpine.data('lazyProbe', () => ({
        message: 'probe-loaded',
    }))
</script>
