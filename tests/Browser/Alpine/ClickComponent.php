<?php

namespace Tests\Browser\Alpine;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ClickComponent extends BaseComponent
{
    public $show = false;

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data="{output: []}">
        <button dusk="show" wire:click="$set('show', true)">Toggle Options</button>

        <div>
            @if($show)
                <button dusk="click" x-on:click="output.push('Clicked')">Click</button>
            @endif
        </div>

        <div>Number of clicks: <span dusk="alpineNumberClicksFired" x-text="output.length"></span></div>
    </div>
</div>
HTML;
    }
}
