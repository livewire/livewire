<?php

namespace Tests\Browser\Morphdom;

use Livewire\Component as BaseComponent;

class ParentComponent extends BaseComponent
{
    public $numbers = [
        1,
        2,
        3,
        4,
    ];

    protected $listeners = [
        'numbersUpdated'
    ];

    public function numbersUpdated($numbers)
    {
        $this->numbers = $numbers;
    }

    public function render()
    {
        return <<< 'HTML'
<div>
    <div dusk="parent-numbers">
        @foreach($numbers as $number)
            <div wire:key="{{ $number }}" @dusk="parent-{{ $number }}">{{ $number }}</div>
        @endforeach
    </div>

    @livewire(Tests\Browser\Morphdom\ChildComponent::class)
</div>
HTML;
    }
}
