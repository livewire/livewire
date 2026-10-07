<?php

namespace LegacyTests\Browser\Loading;

use Livewire\Component as BaseComponent;

class ComponentWithLoadingDelays extends BaseComponent
{
    public $baz = '';

    public function hydrate()
    {
        // Sleep long enough to observe the 1000ms longest delay with room for browser scheduling.
        usleep(1000 * 1500);
    }

    public function render()
    {
        return <<< 'HTML'
<div>
    <button wire:click="$refresh" dusk="load">Load</button>

    <h1 wire:loading.delay.none dusk="delay-none">Loading delay none</h1>    
    <h1 wire:loading.delay.shortest dusk="delay-shortest">Loading delay shortest</h1>
    <h1 wire:loading.delay.shorter dusk="delay-shorter">Loading delay shorter</h1>
    <h1 wire:loading.delay.short dusk="delay-short">Loading delay short</h1>
    <h1 wire:loading.delay.default dusk="delay-default">Loading delay default</h1>    
    <h1 wire:loading.delay dusk="delay">Loading delay</h1>
    <h1 wire:loading.delay.long dusk="delay-long">Loading delay long</h1>
    <h1 wire:loading.delay.longer dusk="delay-longer">Loading delay longer</h1>
    <h1 wire:loading.delay.longest dusk="delay-longest">Loading delay longest</h1>
</div>
HTML;
    }
}
