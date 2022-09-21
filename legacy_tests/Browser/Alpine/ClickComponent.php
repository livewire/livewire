<?php

namespace LegacyTests\Browser\Alpine;

use Livewire\Component as BaseComponent;

class ClickComponent extends BaseComponent
{
    public $show = false;

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data="{ clicks: [] }">
        <button dusk="show" wire:click="$set('show', true)">Toggle Options</button>

        <div>
            @if ($show)
                <button dusk="click" x-on:click="clicks.push('Clicked')">Click</button>
                <button dusk="componentClick" x-data="{ componentClicks: [] }" x-on:click="componentClicks.push('Clicked')">
                    Component Click
                    <div>Number of component clicks: <span dusk="alpineComponentClicksFired" x-text="componentClicks.length"></span></div>
                </button>
            @endif
        </div>

        <div>Number of clicks: <span dusk="alpineClicksFired" x-text="clicks.length"></span></div>
    </div>
</div>
HTML;
    }
}
