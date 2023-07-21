<?php

namespace Livewire\Features\SupportEvents;

use Livewire\Mechanisms\ComponentRegistry;
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

    public function assertDispatched($value, ...$params)
    {
        $result = $this->testDispatched($value, $params);

        PHPUnit::assertTrue($result['test'], "Failed asserting that an event [{$value}] was fired{$result['assertionSuffix']}");

        return $this;
    }

    public function assertNotDispatched($value, ...$params)
    {
        $result = $this->testDispatched($value, $params);

        PHPUnit::assertFalse($result['test'], "Failed asserting that an event [{$value}] was not fired{$result['assertionSuffix']}");

        return $this;
    }

    public function assertDispatchedTo($target, $value, ...$params)
    {
        $this->assertDispatched($value, ...$params);
        $result = $this->testDispatchedTo($target, $value);

        PHPUnit::assertTrue($result, "Failed asserting that an event [{$value}] was fired to {$target}.");

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
        $name = app(ComponentRegistry::class)->getName($target);

        return (bool) collect(data_get($this->effects, 'dispatches'))->first(function ($item) use ($name, $value) {
            return $item['name'] === $value
                && $item['to'] === $name;
        });
    }
}
