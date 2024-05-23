<?php

namespace LegacyTests\Browser\Nesting;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    protected $queryString = ['showChild'];

    public $showChild = false;

    public $key = 'foo';

    public function render()
    {
        return <<<'HTML'
        <div>
            <button wire:click="$toggle('showChild')" dusk="button.toggleChild"></button>

            <button wire:click="$set('key', 'bar')" dusk="button.changeKey"></button>

            @if ($showChild)
                @livewire('nested', key($key))
            @endif
        </div>
        HTML;
    }
}
