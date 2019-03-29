<?php

namespace Livewire\Testing\Concerns;

use Livewire\Testing\TestConnectionHandler;

trait MakesCallsToComponent
{
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

    public function sendMessage($message, $payload)
    {
        $result = (new TestConnectionHandler)
            ->handle([['type' => $message, 'payload' => $payload]], [], $this->serialized);

        $this->updateComponent($result['dom'], $result['serialized']);
    }
}
