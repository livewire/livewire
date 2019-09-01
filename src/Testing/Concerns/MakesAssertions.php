<?php

namespace Livewire\Testing\Concerns;

use Illuminate\Foundation\Testing\Assert as PHPUnit;

trait MakesAssertions
{
    public function assertSet($name, $value)
    {
        PHPUnit::assertEquals((string) $value, $this->instance->getPropertyValue($name));

        return $this;
    }

    public function assertSessionHas($key)
    {
        PHPUnit::assertTrue($this->instance->session()->has($key));

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
