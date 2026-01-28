<?php

new class extends Livewire\Component {
    //
};
?>

<div>
    <div dusk="target">waiting</div>
</div>

<script>
    this.$el.querySelector('[dusk="target"]').textContent = 'js-loaded';
</script>
