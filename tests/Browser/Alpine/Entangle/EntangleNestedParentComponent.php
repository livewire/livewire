<?php

namespace Tests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class EntangleNestedParentComponent extends BaseComponent
{
    public $list = [
        ['id' => 1, 'name' => 'test1'],
    ];

    public function addList()
    {
        $this->list[] = ['id' => (count($this->list) + 1), 'name' => 'test' . (count($this->list) + 1)];
    }

    public function removeList()
    {
        array_pop($this->list);
    }

    public function render()
    {
        return
<<<'HTML'
<div x-data>
    <div dusk="output">
        @foreach($list as $key => $item)
            @livewire(Tests\Browser\Alpine\Entangle\EntangleNestedChildComponent::class, ['item' => $item], key($key))
        @endforeach
    </div>

    <div>
        <button dusk="add" type="button" wire:click="addList">Add</button>
        <button dusk="remove" type="button" wire:click="removeList">Delete</button>
    </div>
</div>
HTML;
    }
}
