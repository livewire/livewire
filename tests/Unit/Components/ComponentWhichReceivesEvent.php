<?php

namespace Tests\Unit\Components;

use Livewire\Component;

class ComponentWhichReceivesEvent extends Component
{
    public function render()
    {
        return view("null-view");
    }
}
