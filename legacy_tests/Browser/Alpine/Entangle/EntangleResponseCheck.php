<?php

namespace LegacyTests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class EntangleResponseCheck extends BaseComponent
{
    public $list = [
        ['id' => 1],
        ['id' => 2],
        ['id' => 3],
        ['id' => 4],
    ];

    public $listUpdatedByAlpine = false;

    public function addList()
    {
        $this->list[] = ['id' => count($this->list)];
    }

    public function updatedList()
    {
        $this->listUpdatedByAlpine = true;
    }

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data="{ list: $wire.entangle('list') }">
        <div dusk="output">{{ $listUpdatedByAlpine ? 'true' : 'false' }}</div>
    </div>

    <div>
        <button dusk="add" type="button" wire:click="addList">Add</button>
    </div>
</div>
HTML;
    }
}
