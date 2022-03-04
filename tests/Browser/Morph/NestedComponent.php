<?php

namespace Tests\Browser\Morph;

use Livewire\Component as BaseComponent;

class NestedComponent extends BaseComponent
{
    public $show = false;

    public function render()
    {
        return <<< 'HTML'
<div>
    <span dusk="nestedOutput">{{ var_export($show) }}</span>
    <button wire:click="$toggle('show')" dusk="toggleNested">Toggle Nested</button>
</div>
HTML;
    }
}
