<?php

namespace Livewire\Features\SupportEvents;

use PHPUnit\Framework\Assert as PHPUnit;

trait TestsEvents
{
    public function emit($event, ...$parameters)
    {
        return parent::call('__emit', $event, ...$parameters);
    }

    public function fireEvent($event, ...$parameters)
    {
        return $this->emit($event, ...$parameters);
    }

    public function assertEmitted($value, ...$params)
    {
        $result = $this->testEmitted($value, $params);

        PHPUnit::assertTrue($result['test'], "Failed asserting that an event [{$value}] was fired{$result['assertionSuffix']}");

        return $this;
    }

    public function assertNotEmitted($value, ...$params)
    {
        $result = $this->testEmitted($value, $params);

        PHPUnit::assertFalse($result['test'], "Failed asserting that an event [{$value}] was not fired{$result['assertionSuffix']}");

        return $this;
    }

    public function assertEmittedTo($target, $value, ...$params)
    {
        $this->assertEmitted($value, ...$params);
        $result = $this->testEmittedTo($target, $value);

        PHPUnit::assertTrue($result, "Failed asserting that an event [{$value}] was fired to {$target}.");

        return $this;
    }

    public function assertEmittedUp($value, ...$params)
    {
        $this->assertEmitted($value, ...$params);
        $result = $this->testEmittedUp($value);

        PHPUnit::assertTrue($result, "Failed asserting that an event [{$value}] was fired up.");

        return $this;
    }

    protected function testEmitted($value, $params)
    {
        $assertionSuffix = '.';

        if (empty($params)) {
            $test = collect(data_get($this->effects, 'emits'))->contains('event', '=', $value);
        } elseif (! is_string($params[0]) && is_callable($params[0])) {
            $event = collect(data_get($this->effects, 'emits'))->first(function ($item) use ($value) {
                return $item['event'] === $value;
            });

            $test = $event && $params[0]($event['event'], $event['params']);
        } else {
            $test = (bool) collect(data_get($this->effects, 'emits'))->first(function ($item) use ($value, $params) {
                return $item['event'] === $value
                    && $item['params'] === $params;
            });

            $encodedParams = json_encode($params);
            $assertionSuffix = " with parameters: {$encodedParams}";
        }

        return [
            'test'            => $test,
            'assertionSuffix' => $assertionSuffix,
        ];
    }

    protected function testEmittedTo($target, $value)
    {
        $target = is_subclass_of($target, Component::class)
            ? $target::getName()
            : $target;

        return (bool) collect(data_get($this->effects, 'emits'))->first(function ($item) use ($target, $value) {
            return $item['event'] === $value
                && $item['to'] === $target;
        });

    }

    protected function testEmittedUp($value)
    {
        return (bool) collect(data_get($this->effects, 'emits'))->first(function ($item) use ($value) {
            return $item['event'] === $value
                && $item['ancestorsOnly'] === true;
        });
    }

    public function assertDispatchedBrowserEvent($name, $data = null)
    {
        $assertionSuffix = '.';

        if (is_null($data)) {
            $test = collect(data_get($this->effects, 'dispatches'))->contains('event', '=', $name);
        } elseif (is_callable($data)) {
            $event = collect(data_get($this->effects, 'dispatches'))->first(function ($item) use ($name) {
                return $item['event'] === $name;
            });

            $test = $event && $data($event['event'], $event['data']);
        } else {
            $test = (bool) collect(data_get($this->effects, 'dispatches'))->first(function ($item) use ($name, $data) {
                return $item['event'] === $name
                    && $item['data'] === $data;
            });
            $encodedData = json_encode($data);
            $assertionSuffix = " with parameters: {$encodedData}";
        }

        PHPUnit::assertTrue($test, "Failed asserting that an event [{$name}] was fired{$assertionSuffix}");

        return $this;
    }

    public function assertNotDispatchedBrowserEvent($name)
    {
        if (! array_key_exists('dispatches', $this->effects)){
            $test = false;
        } else {
            $test = collect($this->effects['dispatches'])->contains('event', '=', $name);
        }

        PHPUnit::assertFalse($test, "Failed asserting that an event [{$name}] was not fired");

        return $this;
    }
}
