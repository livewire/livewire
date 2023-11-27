<?php

namespace Tests;

use Livewire\Component;

class TestComponent extends Component
{
    function save() { $this->validate(); }

    function clear() { $this->clearValidation(); }

    function render()
    {
        return '<div></div>';
    }
}
