<?php

namespace Tests\Browser\Nesting;

use Livewire\Component as BaseComponent;

class ParentWithValuesComponent extends BaseComponent
{
    public $values = [
        1,
        2,
        3,
        4,
        5,
    ];

    public function change()
    {
        array_shift($this->values);
    }

    public function render()
    {
        return <<< 'HTML'
<div>
    <button type="button" wire:click="change" dusk="change-button">Change</button>

    <div dusk="values">
        @foreach($values as $value)
            @livewire(Tests\Browser\Nesting\ChildValueComponent::class, ['value' => $value])
        @endforeach
    </div>
</div>
HTML;
    }
}
