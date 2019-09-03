<?php

namespace Livewire\Testing\Concerns;

use Livewire\Testing\TestConnectionHandler;

trait MakesCallsToComponent
{
    public function fireEvent($event, ...$parameters)
    {
        $this->sendMessage('fireEvent', [
            'event' => $event,
            'params' => $parameters,
        ]);

        return $this;
    }

    public function call($method, ...$parameters)
    {
        return $this->runAction($method, ...$parameters);
    }

    public function runAction($method, ...$parameters)
    {
        $this->sendMessage('callMethod', [
            'method' => $method,
            'params' => $parameters,
            'ref' => null,
        ]);

        return $this;
    }

    public function set($name, $value = null)
    {
        return $this->updateProperty($name, $value);
    }

    public function updateProperty($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->sendMessage('syncInput', [
                    'name' => $key,
                    'value' => $value,
                ]);
            }

            return $this;
        }

        $this->sendMessage('syncInput', [
            'name' => $name,
            'value' => $value,
        ]);

        return $this;
    }

    public function sendMessage($message, $payload)
    {
        $result = (new TestConnectionHandler)
            ->handle([
                'id' => $this->id,
                'name' => $this->name,
                'data' => $this->data,
                'children' => $this->children,
                'checksum' => $this->checksum,
                'gc' => $this->gc,
                'actionQueue' => [['type' => $message, 'payload' => $payload]],
            ]);

        $this->updateComponent($result);
    }
}
