<?php

new class extends Livewire\Component {
    //
};
?>

<div x-data="testAlpineData">
    <div dusk="target" x-text="message"></div>
    <div dusk="js-action" x-text="$wire.$js.moduleGreeting()"></div>
    <div dusk="js-action-state" x-text="$wire.$js.moduleGreeting() instanceof Promise ? 'pending' : 'ready'"></div>
</div>

<script>
    $js('moduleGreeting', () => 'js-action-loaded')

    Alpine.data('testAlpineData', () => ({
        message: 'alpine-data-loaded',
    }))
</script>
