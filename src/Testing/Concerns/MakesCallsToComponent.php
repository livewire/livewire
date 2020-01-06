<?php

namespace Livewire\Testing\Concerns;

use Livewire\Testing\TestConnectionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        try {
            $handler = (new TestConnectionHandler);

            $result = $handler->handle([
                'id' => $this->payload['id'],
                'name' => $this->payload['name'],
                'data' => $this->payload['data'],
                'children' => $this->payload['children'],
                'checksum' => $this->payload['checksum'],
                'errorBag' => $this->payload['errorBag'],
                'actionQueue' => [['type' => $message, 'payload' => $payload]],
            ]);

            if ($validator = $handler->lastValidator) {
                $this->lastValidator = $validator;
            }

            $this->updateComponent($result);
        } catch (HttpException $exception) {
            $this->lastHttpException = $exception;
        }
    }
}
