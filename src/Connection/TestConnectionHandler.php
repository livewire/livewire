<?php

namespace Livewire\Connection;

use Livewire\Livewire;

class TestConnectionHandler extends ConnectionHandler
{
    public function __invoke($actionQueue, $syncQueue, $serialized)
    {
        return $this->handle($actionQueue, $syncQueue, $serialized);
    }
}
