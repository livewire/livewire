<?php

namespace Tests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class DeferDataUpdates extends BaseComponent
{
    public $testing;

    public function render()
    {
        return <<<'HTML'
<div x-data="{ testing:  @entangle('testing').defer }">
    <input type="text" x-model="testing" dusk="input">

    <p>Alpine: <span dusk="output.alpine" x-text="testing"></span></p>

    <p>Livewire: <span dusk="output.livewire">{{$testing}}</span></p>

    <button wire:click.prevent="$refresh" dusk="submit">Submit</button>
</div>
HTML;
    }
}
