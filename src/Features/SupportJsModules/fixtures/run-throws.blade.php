<?php

new class extends Livewire\Component {
    //
};
?>

<div>
    <span dusk="throws-target">server-rendered</span>

    <span dusk="throws-fail-open" x-data="{ message: 'initialized-anyway' }" x-text="message"></span>
</div>

<script>
    throw new Error('boom')
</script>
