<?php

namespace LegacyTests\Browser\Nesting;

use Livewire\Component as BaseComponent;

class NestedComponent extends BaseComponent
{
    public $output = '';

    public function render()
    {
        return <<<'HTML'
        <div>
            <button wire:click="$set('output', 'foo')" dusk="button.nested"></button>

            <span dusk="output.nested">{{ $output }}</span>
        </div>
        HTML;
    }
}
