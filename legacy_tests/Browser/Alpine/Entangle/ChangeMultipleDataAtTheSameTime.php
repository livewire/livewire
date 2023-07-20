<?php

namespace LegacyTests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class ChangeMultipleDataAtTheSameTime extends BaseComponent
{
    public $livewireList = [1,2,3,4];

    public $livewireSearch;

    public function updatedLivewireSearch()
    {
        $this->change();
    }

    public function change()
    {
        $this->livewireList = [5,6,7,8];
    }

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data="{
        alpineList: @entangle('livewireList').live,
        alpineSearch: @entangle('livewireSearch').live
    }">
        <div>
            <h1>Javascript show:</h1>

            <div dusk="output.alpine">
                <ul>
                    <template x-for="item in alpineList">
                        <li x-text="item"></li>
                    </template>
                </ul>
            </div>
        </div>

        <div>
            <h1>Server rendered show:</h1>

            <div dusk="output.livewire">
                <ul>
                @foreach($livewireList as $item)
                    <li>{{ $item }}</li>
                @endforeach
                </ul>
            </div>
        </div>

        <input dusk="search" x-model="alpineSearch" />
        <button dusk="change" wire:click="change">Change List</button>
    </div>
</div>
HTML;
    }
}
