<?php

namespace Tests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class EntangleNestedArray extends BaseComponent
{
    public $list = [];

    public function addList()
    {
        $this->list[] = ['id' => count($this->list)];
    }

    public function removeList()
    {
        array_pop($this->list);
    }

    public function render()
    {
        return
<<<'HTML'
<div>
    <div dusk="output">
        @foreach($list as $key => $item)
            <div wire:key="{{ $key }}" x-data="{ id: $wire.entangle('list.{{ $key }}.id') }">
                <span>Item{{ $item['id'] }}</span>
            </div>
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
