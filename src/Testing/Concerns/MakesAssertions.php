<?php

namespace Livewire\Testing\Concerns;

use Illuminate\Foundation\Testing\Assert as PHPUnit;
use Illuminate\Support\MessageBag;

trait MakesAssertions
{
    public function assertSet($name, $value)
    {
        PHPUnit::assertEquals($value, $this->get($name));

        return $this;
    }

    public function assertNotSet($name, $value)
    {
        PHPUnit::assertNotEquals($value, $this->get($name));

        return $this;
    }

    public function assertCacheHas($key)
    {
        PHPUnit::assertTrue($this->instance->cache()->has($key));

        return $this;
    }

    public function assertSee($value)
    {
        PHPUnit::assertStringContainsString((string) $value,
                                preg_replace('(wire:initial-data=\".+}")', '', $this->dom));

        return $this;
    }

    public function assertDontSee($value)
    {
        PHPUnit::assertStringNotContainsString((string) $value,
                               preg_replace('(wire:initial-data=\".+}")', '', $this->dom));

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

    public function assertHasErrors($keys = [])
    {
        $errors = new MessageBag($this->errorBag ?: []);

        PHPUnit::assertTrue($errors->isNotEmpty(), 'Component has no errors.');

        $keys = (array) $keys;

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertTrue($errors->has($value), "Component missing error: $value");
                continue;
            }

            if (!array_key_exists($key, $errors->messages())) {
                PHPUnit::assertTrue(false, "Component has no errors for [{$key}] attribute");
                continue;
            }

            $errorMessages = implode(" ", $errors->messages()[$key]);

            foreach ((array) $value as $rule) {
                PHPUnit::assertStringContainsStringIgnoringCase($rule, $errorMessages,
                                        "Component has no [{$rule}] errors for [{$key}] attribute.");
            }
        }

        return $this;
    }

    public function assertHasNoErrors($keys = [])
    {
        $errors = new MessageBag($this->errorBag ?: []);

        if (empty($keys)) {
            PHPUnit::assertTrue($errors->isEmpty(), 'Component has errors.');

            return $this;
        }

        $keys = (array) $keys;

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertFalse($errors->has($value), "Component has error: $value");
                continue;
            }

            if (!array_key_exists($key, $this->data)) {
                PHPUnit::assertTrue(false, "Component attribute [{$key}] has not been set");
                continue;
            }

            if (array_key_exists($key, $errors->messages())) {

                $errorMessages = implode(" ",$errors->messages()[$key]);
                foreach ((array) $value as $rule) {
                    PHPUnit::assertStringNotContainsStringIgnoringCase($rule, $errorMessages, "Component has no [{$rule}] errors for [{$key}] attribute.");
                }
                continue;
            }
            PHPUnit::assertTrue(true, "Component has no errors for [{$key}] attribute");
        }

        return $this;
    }
}
