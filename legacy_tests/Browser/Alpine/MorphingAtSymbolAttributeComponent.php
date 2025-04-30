<?php

namespace LegacyTests\Browser\Alpine;

use Livewire\Component as BaseComponent;

class MorphingAtSymbolAttributeComponent extends BaseComponent
{
    public $show = false;

    public function render()
    {
        return <<<'EOD'
<div>
    <div x-data>
        <button wire:click="$toggle('show')" dusk="button">Toggle</button>

        <span @if($show) @@click="hey" @endif dusk="span">hey</span>
    </div>
</div>

EOD;
    }
}
