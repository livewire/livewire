<?php

new class extends Livewire\Component {
    public int $count = 1;

    public function increment()
    {
        $this->count++;
    }
};
?>