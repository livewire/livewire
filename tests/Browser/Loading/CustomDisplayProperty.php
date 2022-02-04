<?php

namespace Tests\Browser\Loading;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class CustomDisplayProperty extends BaseComponent
{
    public function hydrate()
    {
        usleep(500 * 1000);
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                <button wire:click="$refresh" dusk="refresh">Refresh</button>

                <span wire:loading dusk="default">Inline-block</span>
                <span wire:loading.inline-block dusk="inline-block">Inline-block</span>
                <span wire:loading.inline dusk="inline">Inline</span>
                <span wire:loading.block dusk="block">Block</span>
                <span wire:loading.flex dusk="flex">Flex</span>
                <span wire:loading.table dusk="table">Table</span>
                <span wire:loading.grid dusk="grid">Grid</span>
                <span wire:loading.inline-flex dusk="inline-flex">Inline-flex</span>
            </div>
HTML;
    }
}
