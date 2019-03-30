<?php

namespace Livewire\Testing\Concerns;

use Livewire\Testing\TestConnectionHandler;

trait MakesCallsToComponent
{
    public $syncQueue = [];

    public function runAction($method, ...$parameters)
    {
        $this->sendMessage('callMethod', [
            'method' => $method,
            'params' => $parameters,
            'ref' => null,
        ]);

        return $this;
    }

    public function updateProperty($name, $value)
    {
        $this->sendMessage('syncInput', [
            'name' => $name,
            'value' => $value,
        ]);

        return $this;
    }

    public function queueLazilyUpdateProperty($name, $value)
    {
        $this->syncQueue[$name] = $value;

        return $this;
    }

    public function sendMessage($message, $payload)
    {
        $result = (new TestConnectionHandler)
            ->handle([['type' => $message, 'payload' => $payload]], $this->syncQueue, $this->serialized);

        $this->syncQueue = [];

        $this->updateComponent($result);
    }
}
