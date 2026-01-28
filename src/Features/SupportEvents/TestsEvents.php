<?php

namespace Livewire\Features\SupportEvents;

use PHPUnit\Framework\Assert as PHPUnit;

trait TestsEvents
{
    public function dispatch($event, ...$parameters)
    {
        return $this->call('__dispatch', $event, $parameters);
    }

    public function fireEvent($event, ...$parameters)
    {
        return $this->dispatch($event, ...$parameters);
    }

    public function assertDispatched($event, ...$params)
    {
        $result = $this->testDispatched($event, $params);

        PHPUnit::assertTrue($result['test'], "Failed asserting that an event [{$event}] was fired{$result['assertionSuffix']}");

        return $this;
    }

    public function assertNotDispatched($event, ...$params)
    {
        $result = $this->testDispatched($event, $params);

        PHPUnit::assertFalse($result['test'], "Failed asserting that an event [{$event}] was not fired{$result['assertionSuffix']}");

        return $this;
    }

    public function assertDispatchedTo($target, $event, ...$params)
    {
        $this->assertDispatched($event, ...$params);
        $result = $this->testDispatchedTo($target, $event);

        PHPUnit::assertTrue($result, "Failed asserting that an event [{$event}] was fired to {$target}.");

        return $this;
    }

    protected function testDispatched($value, $params)
    {
        $assertionSuffix = '.';

        if (empty($params)) {
            $test = collect(data_get($this->effects, 'dispatches'))->contains('name', '=', $value);
        } elseif (isset($params[0]) && ! is_string($params[0]) && is_callable($params[0])) {
            $event = collect(data_get($this->effects, 'dispatches'))->first(function ($item) use ($value) {
                return $item['name'] === $value;
            });

            $test = $event && $params[0]($event['name'], $event['params']);
        } else {
            $test = (bool) collect(data_get($this->effects, 'dispatches'))->first(function ($item) use ($value, $params) {
                $commonParams = array_intersect_key($item['params'], $params);

                ksort($commonParams);
                ksort($params);

                return $item['name'] === $value
                    && $commonParams === $params;
            });

            $encodedParams = json_encode($params);
            $assertionSuffix = " with parameters: {$encodedParams}";
        }

        return [
            'test'            => $test,
            'assertionSuffix' => $assertionSuffix,
        ];
    }

    protected function testDispatchedTo($target, $value)
    {
        $name = app('livewire.factory')->resolveComponentName($target);

        return (bool) collect(data_get($this->effects, 'dispatches'))->first(function ($item) use ($name, $value) {
            return $item['name'] === $value
                && $item['component'] === $name;
        });
    }
}
