<?php

namespace LegacyTests\Browser\DataBinding\InputText;

use Livewire\Component as BaseComponent;

class EmptyWireModelComponent extends BaseComponent
{
    public function render()
    {
        return <<<'HTML'
<div>
    <input type="text" wire:model dusk="input" />
</div>
HTML;
    }
}
