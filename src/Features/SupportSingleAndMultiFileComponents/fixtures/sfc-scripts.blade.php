<?php

new class extends Livewire\Component {
    public int $count = 1;

    public function increment()
    {
        $this->count++;
    }
};
?>

<div>
    <div dusk="foo" wire:text="count"></div>
</div>

<script>
    $wire.count++
</script>