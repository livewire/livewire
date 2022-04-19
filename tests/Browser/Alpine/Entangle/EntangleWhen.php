<?php

namespace Tests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class EntangleWhen extends BaseComponent
{
    public $can = false;

    public $active = false;

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data="{
        can: @entangle('can'), active: @entangleWhen($can, 'active', false)
    }">
        <div dusk="output.alpine.active" x-text="active"></div>
        <div dusk="output.alpine.can" x-text="can"></div>
        <div dusk="output.livewire.active">{{ $active ? 'true': 'false' }}</div>
        <div dusk="output.livewire.can">{{ $can ? 'true': 'false' }}</div>
        <button dusk="toggleAll" x-on:click="can = !can; active = !active">Toggle all</button>
        <button dusk="toggleCan" x-on:click="can = !can">Toggle can</button>
    </div>
</div>
HTML;
    }
}
