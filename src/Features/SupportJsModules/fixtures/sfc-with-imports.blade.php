<?php

new class extends Livewire\Component {
    //
};
?>

<div>
    <div dusk="target">waiting</div>
</div>

<script>
import { greeting } from '/test-module.js'

this.$el.querySelector('[dusk="target"]').textContent = greeting
</script>
