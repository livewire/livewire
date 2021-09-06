<?php

namespace Tests\Browser\DetectMultipleRootElements;

use Livewire\Component as BaseComponent;

class ComponentWithNestedSingleRootElement extends BaseComponent
{
    public function render()
    {
        return
<<<'HTML'
<div>
    Nested: @livewire(\Tests\Browser\DetectMultipleRootElements\ComponentWithSingleRootElement::class)
    <span>Dummy Element</span>
</div>
HTML;
    }
}
