<?php

new class extends Livewire\Component {
    //
};
?>

<div>
    <div dusk="first-page">First page</div>

    <livewire:testns::alpine-data.index />

    <a href="/alpine-data-page-2" wire:navigate dusk="link">Go to second page</a>
</div>
