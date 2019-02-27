<?php

namespace Livewire\Testing;

use Livewire\Connection\ConnectionHandler;
use Livewire\Testing\TestableLivewireComponentWrapper;

class TestConnectionHandler extends ConnectionHandler
{
    public function wrap($instance)
    {
        return TestableLivewireComponentWrapper::wrap($instance);
    }
}
