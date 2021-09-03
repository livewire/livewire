<?php

namespace Tests\Browser\DetectMultipleRootElements;

use Livewire\Component as BaseComponent;

class ComponentWithSingleRootElement extends BaseComponent
{
    public function render()
    {
        return
<<<'HTML'
<div>Only Element</div>
HTML;
    }
}
