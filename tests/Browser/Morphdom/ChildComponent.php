<?php

namespace Tests\Browser\Morphdom;

use Livewire\Component as BaseComponent;

class ChildComponent extends BaseComponent
{
    public $numbers = [
        1,
        2,
    ];

    public function addNumber()
    {
        $this->numbers[] = end($this->numbers) + 1;

        $this->emit('numbersUpdated', $this->numbers);
    }

    public function render()
    {
        return <<< 'HTML'
<div>
    <div dusk="child-numbers">
        @foreach($numbers as $number)
            <div wire:key="{{ $number }}" dusk="child-{{ $number }}">{{ $number }}</div>
        @endforeach
    </div>

    <button type="button" wire:click="addNumber" dusk="add-number">Add Number</button>
</div>
HTML;
    }
}
