<?php

namespace LegacyTests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class DeferDataUpdates extends BaseComponent
{
    public $testing = null;

    public function render()
    {
        return <<<'HTML'
<div x-data="{ testing:  @entangle('testing') }">
    <input type="text" x-model="testing" dusk="input">

    <p>Alpine: <span dusk="output.alpine" x-text="testing"></span></p>

    <p>Livewire: <span dusk="output.livewire">{{$testing}}</span></p>

    <button wire:click.prevent="$refresh" dusk="submit">Submit</button>
</div>
HTML;
    }
}
