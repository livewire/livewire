<?php

namespace Tests\Browser\Alpine;

use Livewire\Component as BaseComponent;

class ClickComponent extends BaseComponent
{
    public $show = false;

    public $items = [1, 2, 3];

    public function reverseItems()
    {
        $this->items = array_reverse($this->items);
    }

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data="{ clicks: [], loopClicks: [] }">
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

        <div>
            <button wire:click="reverseItems()" dusk="reverseItems">Reverse Items</button>

            @foreach ($items as $item)
                <span wire:key='{{ $item }}' dusk='loopClick{{ $item }}' x-on:click="loopClicks.push('Clicked')">Click {{ $item }}</span>
            @endforeach
        </div>

        <div>Number of loop clicks: <span dusk="alpineLoopClicksFired" x-text="loopClicks.length"></span></div>
    </div>
</div>
HTML;
    }
}
