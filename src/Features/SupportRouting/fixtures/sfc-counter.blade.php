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
    <span>Count: {{ $count }}</span>
    <button wire:click="increment">Increment</button>
</div>