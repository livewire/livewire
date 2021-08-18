<?php

namespace Tests\Browser\DetectMultipleRootElements;

use Livewire\Component as BaseComponent;

class ComponentWithCommentAsFirstElement extends BaseComponent
{
    public function render()
    {
        return
<<<'HTML'
<!-- A comment here -->
<div>Element</div>
HTML;
    }
}
