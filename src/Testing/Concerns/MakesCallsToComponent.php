<?php

namespace Livewire\Testing\Concerns;

use Livewire\Testing\TestConnectionHandler;
use Symfony\Component\DomCrawler\Crawler;

trait MakesCallsToComponent
{
    public function callMethod($method, $parameters = [])
    {
        $this->sendMessage('callMethod', [
            'method' => $method,
            'params' => $parameters,
            'ref' => null,
        ]);

        return $this;
    }

    public function syncInput($name, $value)
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
            ->handle($message, $payload, $this->serialized);

        $this->updateComponent($result['dom'], $result['serialized']);
    }
}
