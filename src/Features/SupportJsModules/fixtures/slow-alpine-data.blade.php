<?php

new class extends Livewire\Component {
    //
};
?>

<div dusk="wrapper" x-data="slowAlpineData" x-cloak>
    <span dusk="target" x-text="message"></span>
</div>

<script>
    import '/slow-module.js'

    window.slowModuleRan = true

    Alpine.data('slowAlpineData', () => ({
        message: 'slow-data-loaded',
    }))
</script>
