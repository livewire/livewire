<?php

namespace Livewire\Factory\Fixtures;

use Livewire\Component;

class SimpleComponent extends Component
{
    public $message = 'Hello World';

    public function render()
    {
        return '<div>{{ $message }}</div>';
    }
}
