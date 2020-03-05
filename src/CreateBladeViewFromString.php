<?php

namespace Livewire;

use Illuminate\View\Component;

class CreateBladeViewFromString extends Component
{
    public function __invoke($contents)
    {
        return $this->createBladeViewFromString(app('view'), $contents);
    }

    public function render()
    {
        //
    }
}
