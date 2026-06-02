<?php

use Carbon\Carbon;

new class extends Livewire\Component {
    public function with()
    {
        return ['timestamp' => Carbon::now()->timestamp];
    }
};
?>

<div>
    <div>Outside island: {{ Carbon::createFromTimestamp($timestamp)->timestamp }}</div>

    @island
        <div>Inside island: {{ Carbon::createFromTimestamp($timestamp)->timestamp }}</div>
    @endisland
</div>
