<?php

namespace Tests\Browser\Alpine\Emit;

use Livewire\Component as BaseComponent;

class EmitNestedComponent extends BaseComponent
{
    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data>
        <button dusk="emitUp" @click="$wire.emitUp('emit', 'emitUp')">Emit Up</button>
    </div>
    
    <div x-data>
        <button dusk="emitTo" @click="$wire.emitTo('tests.browser.alpine.emit.emit-component', 'emit', 'emitTo')">Emit To</button>
    </div>
</div>
HTML;
    }
}
