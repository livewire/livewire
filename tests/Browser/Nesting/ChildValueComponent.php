<?php

namespace Tests\Browser\Nesting;

use Livewire\Component as BaseComponent;

class ChildValueComponent extends BaseComponent
{
    public $value;

    public function render()
    {
        return <<< 'HTML'
<div>
    Value {{ $value }}
</div>
HTML;
    }
}
