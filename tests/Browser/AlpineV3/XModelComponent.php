<?php

namespace Tests\Browser\AlpineV3;

use Livewire\Component as BaseComponent;

class XModelComponent extends BaseComponent
{
    public $show = false;

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data="{ checks: [] }">
        <button dusk="show" wire:click="$toggle('show')">Toggle Options</button>

        <div>
            @if ($show)
                <input dusk="plz-check-me-caleb" type="checkbox" x-model.number="checks" value="1" /> Check me
            @endif
        </div>

        <div>Checks value: <span dusk="alpineChecksValue" x-text="checks"></span></div>
        <div>Number of checks: <span dusk="alpineChecksLength" x-text="checks.length"></span></div>
    </div>
</div>
HTML;
    }
}
