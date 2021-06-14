<?php

namespace Tests\Browser\Alpine\Transition;

use Livewire\Component as BaseComponent;

class DollarSignWireComponent extends BaseComponent
{
    public $show = true;
    public $changeDom = false;

    public function render()
    {
        return <<<'EOD'
<div>
    <div x-data>
        <button wire:click="$toggle('show')" dusk="button">Toggle</button>
        <button wire:click="$toggle('changeDom')" dusk="change-dom">Change DOM</button>

        <div x-show="$wire.show" dusk="outer">
            <div x-show.transition.duration.250ms="$wire.show" x-transition.duration.250ms dusk="inner">
                <h1>@if ($changeDom) @json($show) @else static-filler @endif</h1>
            </div>
        </div>
    </div>
</div>

EOD;
    }
}
