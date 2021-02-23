<?php

namespace Livewire\Testing\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Constraints\SeeInOrder;
use PHPUnit\Framework\Assert as PHPUnit;

trait MakesAssertions
{
    public function assertSet($name, $value)
    {
        if (! is_string($value) && is_callable($value)) {
            PHPUnit::assertTrue($value($this->get($name)));
        } else {
            PHPUnit::assertEquals($value, $this->get($name));
        }

        return $this;
    }

    public function assertNotSet($name, $value)
    {
        PHPUnit::assertNotEquals($value, $this->get($name));

        return $this;
    }

    public function assertCount($name, $value)
    {
        PHPUnit::assertCount($value, $this->get($name));

        return $this;
    }

    public function assertPayloadSet($name, $value)
    {
        if (is_callable($value)) {
            PHPUnit::assertTrue($value(data_get($this->payload['serverMemo']['data'], $name)));
        } else {
            PHPUnit::assertEquals($value, data_get($this->payload['serverMemo']['data'], $name));
        }

        return $this;
    }

    public function assertPayloadNotSet($name, $value)
    {
        if (is_callable($value)) {
            PHPUnit::assertFalse($value(data_get($this->payload['serverMemo']['data'], $name)));
        } else {
            PHPUnit::assertNotEquals($value, data_get($this->payload['serverMemo']['data'], $name));
        }

        return $this;
    }

    public function assertSee($value)
    {
        PHPUnit::assertStringContainsString(
            e($value),
            $this->stripOutInitialData($this->lastRenderedDom)
        );

        return $this;
    }

    public function assertDontSee($value)
    {
        PHPUnit::assertStringNotContainsString(
            e($value),
            $this->stripOutInitialData($this->lastRenderedDom)
        );

        return $this;
    }

    public function assertSeeHtml($value)
    {
        PHPUnit::assertStringContainsString(
            $value,
            $this->stripOutInitialData($this->lastRenderedDom)
        );

        return $this;
    }

    public function assertDontSeeHtml($value)
    {
        PHPUnit::assertStringNotContainsString(
            $value,
            $this->stripOutInitialData($this->lastRenderedDom)
        );

        return $this;
    }

    public function assertSeeHtmlInOrder(array $values)
    {
        PHPUnit::assertThat(
            $values,
            new SeeInOrder($this->stripOutInitialData($this->lastRenderedDom))
        );

        return $this;
    }

    public function assertSeeInOrder(array $values)
    {
        PHPUnit::assertThat(
            array_map('e', ($values)),
            new SeeInOrder($this->stripOutInitialData($this->lastRenderedDom))
        );

        return $this;
    }

    protected function stripOutInitialData($subject)
    {
        return preg_replace('(wire:initial-data=\".+}")', '', $subject);
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

    protected function testEmitted($value, $params)
    {
        $assertionSuffix = '.';

        if (empty($params)) {
            $test = collect(data_get($this->payload, 'effects.emits'))->contains('event', '=', $value);
        } elseif (is_callable($params[0])) {
            $event = collect(data_get($this->payload, 'effects.emits'))->first(function ($item) use ($value) {
                return $item['event'] === $value;
            });

            $test = $event && $params[0]($event['event'], $event['params']);
        } else {
            $test = (bool) collect(data_get($this->payload, 'effects.emits'))->first(function ($item) use ($value, $params) {
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

    public function assertDispatchedBrowserEvent($name, $data = null)
    {
        $assertionSuffix = '.';

        if (is_null($data)) {
            $test = collect($this->payload['effects']['dispatches'])->contains('event', '=', $name);
        } elseif (is_callable($data)) {
            $event = collect($this->payload['effects']['dispatches'])->first(function ($item) use ($name) {
                return $item['event'] === $name;
            });

            $test = $event && $data($event['event'], $event['data']);
        } else {
            $test = (bool) collect($this->payload['effects']['dispatches'])->first(function ($item) use ($name, $data) {
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
        $errors = $this->lastErrorBag;

        PHPUnit::assertTrue($errors->isNotEmpty(), 'Component has no errors.');

        $keys = (array) $keys;

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertTrue($errors->has($value), "Component missing error: $value");
            } else {
                $failed = optional($this->lastValidator)->failed() ?: [];
                $rules = array_keys(Arr::get($failed, $key, []));

                foreach ((array)$value as $rule) {
                    PHPUnit::assertContains(Str::studly($rule), $rules, "Component has no [{$rule}] errors for [{$key}] attribute.");
                }
            }
        }

        return $this;
    }

    public function assertHasNoErrors($keys = [])
    {
        $errors = $this->lastErrorBag;

        if (empty($keys)) {
            PHPUnit::assertTrue($errors->isEmpty(), 'Component has errors: "' . implode('", "', $errors->keys()) . '"');

            return $this;
        }

        $keys = (array) $keys;

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertFalse($errors->has($value), "Component has error: $value");
            } else {
                $failed = optional($this->lastValidator)->failed() ?: [];
                $rules = array_keys(Arr::get($failed, $key, []));

                foreach ((array) $value as $rule) {
                    PHPUnit::assertNotContains(Str::studly($rule), $rules, "Component has [{$rule}] errors for [{$key}] attribute.");
                }
            }
        }

        return $this;
    }

    public function assertRedirect($uri = null)
    {
        PHPUnit::assertArrayHasKey(
            'redirect',
            $this->payload['effects'],
            'Component did not perform a redirect.'
        );

        if (! is_null($uri)) {
            PHPUnit::assertSame(url($uri), url($this->payload['effects']['redirect']));
        }

        return $this;
    }

    public function assertViewIs($name)
    {
        PHPUnit::assertEquals($name, $this->lastRenderedView->getName());

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
