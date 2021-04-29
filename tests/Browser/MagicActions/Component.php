<?php

namespace Tests\Browser\MagicActions;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $active = false;
    public $foo = ['bar' => ['baz' => false]];

    public function render()
    {
        return
<<<'HTML'
<div>
    <div dusk="output">{{ $active ? "true" : "false" }}</div>
    <button wire:click="$toggle('active')" dusk="toggle">Toggle Property</button>

    <div dusk="outputNested">{{ $foo['bar']['baz'] ? "true" : "false" }}</div>
    <button wire:click="$toggle('foo.bar.baz')" dusk="toggleNested">Toggle Nested</button>
</div>
HTML;
    }
}
