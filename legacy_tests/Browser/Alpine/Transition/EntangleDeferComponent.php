<?php

namespace LegacyTests\Browser\Alpine\Transition;

use Livewire\Component as BaseComponent;

class EntangleDeferComponent extends BaseComponent
{
    public $show = true;
    public $changeDom = false;

    public function render()
    {
        return <<<'EOD'
<div>
    <div x-data="{ show: @entangle('show') }">
        <button x-on:click="show = ! show" dusk="button">Alpine Toggle</button>
        <button wire:click="$toggle('show')" dusk="livewire-button">Livewire Toggle</button>
        <button wire:click="$toggle('changeDom')" dusk="change-dom">Change DOM</button>

        <div x-show="show" dusk="outer">
            <div x-show.transition.duration.250ms="show" x-transition.duration.250ms dusk="inner">
                <h1>@if ($changeDom) @json($show) @else static-filler @endif</h1>
            </div>
        </div>
    </div>
</div>

EOD;
    }
}
