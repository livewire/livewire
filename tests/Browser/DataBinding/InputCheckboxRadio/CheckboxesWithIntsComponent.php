<?php

namespace Tests\Browser\DataBinding\InputCheckboxRadio;

use Livewire\Component as BaseComponent;

class CheckboxesWithIntsComponent extends BaseComponent
{
    public $data = [2,3];

    public function render()
    {
        return <<< 'HTML'
<div>
    <input dusk="int1" wire:model="data" type="checkbox" value="1" />
    <input dusk="int2" wire:model="data" type="checkbox" value="2" />
    <input dusk="int3" wire:model="data" type="checkbox" value="3" />

    <div dusk="output">{{ var_export($data) }}</div>
</div>
HTML;
    }
}
