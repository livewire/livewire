<?php

new class extends Livewire\Component {
    public int $count = 0;

    public function increment()
    {
        $this->count++;
    }
};
?>

<div>
    @island
    island test 1
    @endisland

    @island
    island test 2
    @endisland

    @island
    island test 3
    @endisland

    @island
    island test 4
    @endisland

    @island
    island test 5
    @endisland

    @island
    island test 6
    @endisland

    @island
    island test 7
    @endisland

    @island
    island test 8
    @endisland

    @island
    island test 9
    @endisland

    @island
    island test 10
    @endisland

    @island
    island test 11
    @endisland
</div>
