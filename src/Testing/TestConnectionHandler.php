<?php

namespace Livewire\Testing;

use Livewire\Connection\ConnectionHandler;
use Livewire\Testing\TestableComponentWrapper;

class TestConnectionHandler extends ConnectionHandler
{
    public function wrap($instance)
    {
        return TestableComponentWrapper::wrap($instance);
    }
}
