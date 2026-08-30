<?php

new class extends Livewire\Component {
    //
};
?>

<div>
    <span dusk="broken-target">server-rendered</span>

    <span dusk="broken-binding" x-data="brokenImportData" x-text="message"></span>

    <span dusk="broken-fail-open" x-data="{ message: 'initialized-anyway' }" x-text="message"></span>
</div>

<script>
    import '/missing-module.js'

    Alpine.data('brokenImportData', () => ({
        message: 'never',
    }))
</script>
