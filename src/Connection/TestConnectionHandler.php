<?php

namespace Livewire\Connection;

use Livewire\Livewire;

class TestConnectionHandler extends ConnectionHandler
{
    public static function runAction($action, $serialized)
    {
        return (new static)([[
            'type' => 'callMethod',
            'payload' => [
                'method' => $action,
                'params' => [],
            ],
        ]], [], $serialized);
    }

    public function __invoke($actionQueue, $syncQueue, $serialized)
    {
        return $this->handle($actionQueue, $syncQueue, $serialized);
    }
}
