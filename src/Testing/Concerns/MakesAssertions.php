<?php

namespace Livewire\Testing\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Assert as PHPUnit;

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

    public function assertSee($value)
    {
        PHPUnit::assertStringContainsString((string) $value, preg_replace('(wire:initial-data=\".+}")', '', $this->payload['dom']));

        return $this;
    }

    public function assertDontSee($value)
    {
        PHPUnit::assertStringNotContainsString((string) $value, preg_replace('(wire:initial-data=\".+}")', '', $this->payload['dom']));

        return $this;
    }

    public function assertEmitted($value, ...$params)
    {
        $assertionSuffix = '.';

        if (empty($params)) {
            $test = collect($this->payload['eventQueue'])->contains('event', '=', $value);
        } elseif (is_callable($params[0])) {
            $event = collect($this->payload['eventQueue'])->first(function ($item) use ($value) {
                return $item['event'] === $value;
            });

            $test = $event && $params[0]($event['event'], $event['params']);
        } else {
            $test = !! collect($this->payload['eventQueue'])->first(function ($item) use ($value, $params) {
                return $item['event'] === $value
                    && $item['params'] === $params;
            });
            $encodedParams = json_encode($params);
            $assertionSuffix = " with parameters: {$encodedParams}";
        }

        PHPUnit::assertTrue($test, "Failed asserting that an event [{$value}] was fired{$assertionSuffix}");

        return $this;
    }

    public function assertDispatchedBrowserEvent($name, $data = null)
    {
        $assertionSuffix = '.';

        if (is_null($data)) {
            $test = collect($this->payload['dispatchQueue'])->contains('event', '=', $name);
        } elseif (is_callable($data)) {
            $event = collect($this->payload['dispatchQueue'])->first(function ($item) use ($name) {
                return $item['event'] === $name;
            });

            $test = $event && $data($event['event'], $event['data']);
        } else {
            $test = !! collect($this->payload['dispatchQueue'])->first(function ($item) use ($name, $data) {
                return $item['event'] === $name
                    && $item['data'] === $data;
            });
            $encodedData = json_encode($data);
            $assertionSuffix = " with parameters: {$encodedData}";
        }

        PHPUnit::assertTrue($test, "Failed asserting that an event [{$name}] was fired{$assertionSuffix}");

        return $this;
    }

    public function assertHasErrors($keys = [])
    {
        $errors = new MessageBag($this->payload['errorBag'] ?: []);

        PHPUnit::assertTrue($errors->isNotEmpty(), 'Component has no errors.');

        $keys = (array) $keys;

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertTrue($errors->has($value), "Component missing error: $value");
            } else {
                $rules = array_keys(Arr::get($this->lastValidator->failed(), $key, []));
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
        $errors = new MessageBag($this->payload['errorBag'] ?: []);

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

    public function assertRedirect($uri = null)
    {
        PHPUnit::assertIsString(
            $this->payload['redirectTo'],
            'Component did not perform a redirect.'
        );

        if (! is_null($uri)) {
            PHPUnit::assertSame(url($uri), url($this->payload['redirectTo']));
        }

        return $this;
    }

    public function assertViewHas($key, $value = null)
    {
        if (is_null($value)) {
            PHPUnit::assertArrayHasKey($key, $this->lastRenderedView->gatherData());
        } elseif ($value instanceof \Closure) {
            PHPUnit::assertTrue($value($this->lastRenderedView->gatherData()[$key]));
        } elseif ($value instanceof Model) {
            PHPUnit::assertTrue($value->is($this->lastRenderedView->gatherData()[$key]));
        } else {
            PHPUnit::assertEquals($value, $this->lastRenderedView->gatherData()[$key]);
        }

        return $this;
    }
}
