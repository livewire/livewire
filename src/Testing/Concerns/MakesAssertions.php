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

    public function assertHasErrors($keys = [])
    {
        $errors = new MessageBag($this->errorBag ?: []);

        PHPUnit::assertTrue($errors->isNotEmpty(), 'Component has no errors.');

        $keys = (array) $keys;

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertTrue($errors->has($value), "Component missing error: $value");
            } else {
                $rules = array_keys($this->lastValidator->failed()[$key]);
                $lowerCaseRules = array_map('strtolower', $rules);

                foreach ((array) $value as $rule) {
                    PHPUnit::assertContains($rule, $lowerCaseRules, "Component has no [{$rule}] errors for [{$key}] attribute.");
                }
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
            } else {
                $rules = array_keys($this->lastValidator->failed()[$key]);
                $lowerCaseRules = array_map('strtolower', $rules);

                foreach ((array) $value as $rule) {
                    PHPUnit::assertNotContains($rule, $lowerCaseRules, "Component has [{$rule}] errors for [{$key}] attribute.");
                }
            }
        }

        return $this;
    }

    public function assertStatus($status)
    {
        $actual = $this->lastHttpException->getStatusCode();

        PHPUnit::assertTrue(
            $actual === $status,
            "Expected status code [{$status}] but received [{$actual}]."
        );

        return $this;
    }

    public function assertNotFound()
    {
        $actual = $this->lastHttpException->getStatusCode();

        PHPUnit::assertTrue(
            $actual === 404,
            'Response status code ['.$actual.'] is not a not found status code.'
        );

        return $this;
    }

    public function assertForbidden()
    {
        $actual = $this->lastHttpException->getStatusCode();

        PHPUnit::assertTrue(
            $actual === 403,
            'Response status code ['.$actual.'] is not a forbidden status code.'
        );

    }

    public function assertUnauthorized()
    {
        $actual = $this->lastHttpException->getStatusCode();

        PHPUnit::assertTrue(
            $actual === 401,
            'Response status code ['.$actual.'] is not an unauthorized status code.'
        );

        return $this;
    }
}
