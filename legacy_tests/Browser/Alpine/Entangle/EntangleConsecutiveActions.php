<?php

namespace LegacyTests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class EntangleConsecutiveActions extends BaseComponent
{
    public $livewireList = [];

    public function add()
    {
        $this->livewireList[] = count($this->livewireList);
    }

    public function render()
    {
        return
<<<'HTML'
<div x-data="{ alpineList: @entangle('livewireList').live }">
    <div>Alpine</div>
    <div dusk="alpineOutput">
        <template x-for="(item, key) in alpineList" :key="key">
            <div x-text="item"></div>
        </template>
    </div>

    <div>Livewire</div>
    <div dusk="livewireOutput">
        @foreach($livewireList as $key => $item)
            <div>{{ $item }}</div>
        @endforeach
    </div>

    <div>
        <button dusk="alpineAdd" type="button" x-on:click="alpineList.push(alpineList.length)">Add Alpine</button>
        <button dusk="livewireAdd" type="button" wire:click="add">Add Livewire</button>
    </div>
</div>
HTML;
    }
}
