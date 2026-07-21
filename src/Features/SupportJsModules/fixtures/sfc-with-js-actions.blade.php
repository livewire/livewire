<?php

new class extends Livewire\Component {
    //
};
?>

<div>
    <div dusk="target" wire:text="$js.outer()">waiting</div>
</div>

<script>
$js.inner = () => 'js-actions-composed'

$js.outer = () => $js.inner()
</script>
