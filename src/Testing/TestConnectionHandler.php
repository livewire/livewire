<?php

namespace Livewire\Testing;

use Livewire\Connection\ConnectionHandler;

class TestConnectionHandler extends ConnectionHandler
{
    public function wrap($instance)
    {
        return TestableComponentWrapper::wrap($instance);
    }
}
