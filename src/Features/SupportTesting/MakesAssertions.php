<?php

namespace Livewire\Features\SupportTesting;

use Illuminate\Testing\Constraints\SeeInOrder;
use PHPUnit\Framework\Assert as PHPUnit;
use Illuminate\Support\Arr;

trait MakesAssertions
{
    function assertSee($values, $escape = true, $stripInitialData = true)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringContainsString(
                $escape ? e($value): $value,
                $this->html($stripInitialData)
            );
        }

        return $this;
    }

    function assertDontSee($values, $escape = true, $stripInitialData = true)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringNotContainsString(
                $escape ? e($value): $value,
                $this->html($stripInitialData)
            );
        }

        return $this;
    }

    function assertSeeHtml($values)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringContainsString(
                $value,
                $this->html()
            );
        }

        return $this;
    }

    function assertSeeHtmlInOrder($values)
    {
        PHPUnit::assertThat(
            $values,
            new SeeInOrder($this->html())
        );

        return $this;
    }

    function assertDontSeeHtml($values)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringNotContainsString(
                $value,
                $this->html()
            );
        }

        return $this;
    }

    function assertSeeText($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map('e', ($value)) : $value;

        $content = $this->html();

        tap(strip_tags($content), function ($content) use ($values) {
            foreach ($values as $value) {
                PHPUnit::assertStringContainsString((string) $value, $content);
            }
        });

        return $this;
    }

    function assertSet($name, $value, $strict = false)
    {
        $actual = $this->get($name);

        if (! is_string($value) && is_callable($value)) {
            PHPUnit::assertTrue($value($actual));
        } else {
            $strict ? PHPUnit::assertSame($value, $actual) : PHPUnit::assertEquals($value, $actual);
        }

        return $this;
    }

    function assertNotSet($name, $value, $strict = false)
    {
        $actual = $this->get($name);

        $strict ? PHPUnit::assertNotSame($value, $actual) : PHPUnit::assertNotEquals($value, $actual);

        return $this;
    }

    function assertSetStrict($name, $value)
    {
        $this->assertSet($name, $value, true);

        return $this;
    }

    function assertNotSetStrict($name, $value)
    {
        $this->assertNotSet($name, $value, true);

        return $this;
    }

    function assertCount($name, $value)
    {
        PHPUnit::assertCount($value, $this->get($name));

        return $this;
    }

    function assertSnapshotSet($name, $value, $strict = false)
    {
        $data = $this->lastState->getSnapshotData();

        if (is_callable($value)) {
            PHPUnit::assertTrue($value(data_get($data, $name)));
        } else {
            $strict ? PHPUnit::assertSame($value, data_get($data, $name)) : PHPUnit::assertEquals($value, data_get($data, $name));
        }

        return $this;
    }

    function assertSnapshotNotSet($name, $value, $strict = false)
    {
        $data = $this->lastState->getSnapshotData();

        if (is_callable($value)) {
            PHPUnit::assertFalse($value(data_get($data, $name)));
        } else {
            $strict ? PHPUnit::assertNotSame($value, data_get($data, $name)) : PHPUnit::assertNotEquals($value, data_get($data, $name));
        }

        return $this;
    }

    public function assertReturned($value)
    {
        $data = data_get($this->lastState->getEffects(), 'returns.0');

        if (is_callable($value)) {
            PHPUnit::assertTrue($value($data));
        } else {
            PHPUnit::assertEquals($value, $data);
        }

        return $this;
    }
}
