<?php

namespace Livewire\Testing\Concerns;

use Illuminate\Foundation\Testing\Assert as PHPUnit;

trait MakesAssertions
{
    public function assertSet($name, $value)
    {
        PHPUnit::assertEquals($value, $this->get($name));

        return $this;
    }

    public function assertCacheHas($key)
    {
        PHPUnit::assertTrue($this->instance->cache()->has($key));

        return $this;
    }

    public function assertSee($value)
    {
        PHPUnit::assertStringContainsString((string) $value, preg_replace('(wire:initial-data=\".+}")', '', $this->dom));

        return $this;
    }

    public function assertDontSee($value)
    {
        PHPUnit::assertStringNotContainsString((string) $value, preg_replace('(wire:initial-data=\".+}")', '', $this->dom));

        return $this;
    }

    public function assertEmitted($value, ...$params)
    {
        $assertionSuffix = '.';

        if (empty($params)) {
            $test = collect($this->eventQueue)->contains('event', '=', $value);
        } elseif (is_callable($params[0])) {
            $event = collect($this->eventQueue)->first(function ($item) use ($value) {
                return $item['event'] === $value;
            });

            $test = $event && $params[0]($event['event'], $event['params']);
        } else {
            $test = !! collect($this->eventQueue)->first(function ($item) use ($value, $params) {
                return $item['event'] === $value
                    && $item['params'] === $params;
            });
            $encodedParams = json_encode($params);
            $assertionSuffix = " with parameters: {$encodedParams}";
        }

        PHPUnit::assertTrue($test, "Failed asserting that an event [{$value}] was fired{$assertionSuffix}");

        return $this;
    }
}
