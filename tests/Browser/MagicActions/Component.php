<?php

namespace Tests\Browser\MagicActions;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $foo = ['bar' => ['baz' => false]];

    public function render()
    {
        return
<<<'HTML'
<div>
    <div dusk="output">{{ $foo['bar']['baz'] ? "true" : "false" }}</div>
    <button wire:click="$toggle('foo.bar.baz')" dusk="toggle">Toggle Nested</button>
</div>
HTML;
    }
}
