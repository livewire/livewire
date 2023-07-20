<?php

namespace LegacyTests\Browser\Alpine\Dispatch;

use Livewire\Component as BaseComponent;

class DispatchNestedComponent extends BaseComponent
{
    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data>
        <button dusk="dispatchTo" @click="$wire.dispatchTo('parent', 'dispatch', { name: 'dispatchTo' })">Dispatch To</button>
    </div>
</div>
HTML;
    }
}
