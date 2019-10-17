<?php

namespace Livewire\Testing\Concerns;

use Illuminate\Foundation\Testing\Assert as PHPUnit;

trait MakesAssertions
{
    public function assertSet($name, $value)
    {
        PHPUnit::assertEquals($value, $this->instance->getPropertyValue($name));

        return $this;
    }

    public function assertCacheHas($key)
    {
        PHPUnit::assertTrue($this->instance->cache()->has($key));

        return $this;
    }

    public function assertSee($value)
    {
        PHPUnit::assertStringContainsString((string) $value, preg_replace('(wire:data=\".+}")', '', $this->dom));

        return $this;
    }

    public function assertDontSee($value)
    {
        PHPUnit::assertStringNotContainsString((string) $value, preg_replace('(wire:data=\".+}")', '', $this->dom));

        return $this;
    }
}
