<?php

namespace Livewire\Connection;

use Livewire\Livewire;
use Livewire\TestableLivewireComponentWrapper;

class TestConnectionHandler extends ConnectionHandler
{
    public function wrap($instance)
    {
        return TestableLivewireComponentWrapper::wrap($instance);
    }
}
