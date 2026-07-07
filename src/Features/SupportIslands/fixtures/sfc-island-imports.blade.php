<?php

use Carbon\Carbon;

new class extends Livewire\Component {
    public function with()
    {
        return ['timestamp' => Carbon::parse('2024-01-01')->timestamp];
    }
};
?>

<div>
    @island
        <span>year: {{ Carbon::createFromTimestamp($timestamp)->year }}</span>
    @endisland
</div>
