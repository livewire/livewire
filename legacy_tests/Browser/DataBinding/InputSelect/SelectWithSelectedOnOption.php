<?php

namespace LegacyTests\Browser\DataBinding\InputSelect;

use Livewire\Component as BaseComponent;

class SelectWithSelectedOnOption extends BaseComponent
{
    public $selectedOption = '3';

    public function render()
    {
        return <<<'HTML'
<div>
    <h1 dusk="output">{{ $selectedOption }}</h1>
    <select wire:model.live="selectedOption" dusk="select-input">
        <option value="1" @if($selectedOption == '1') selected @endif>Option 1</option>
        <option value="2" @if($selectedOption == '2') selected @endif>Option 2</option>
        <option value="3" @if($selectedOption == '3') selected @endif>Option 3</option>
        <option value="4" @if($selectedOption == '4') selected @endif>Option 4</option>
        <option value="5" @if($selectedOption == '5') selected @endif>Option 5</option>
    </select>
</div>
HTML;
    }
}
