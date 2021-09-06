<?php

namespace Tests\Browser\DetectMultipleRootElements;

use Livewire\Component as BaseComponent;

class ComponentWithMultipleRootElements extends BaseComponent
{
    public function render()
    {
        return
<<<'HTML'
<div>Element 1</div>
<div>Element 2</div>
HTML;
    }
}
