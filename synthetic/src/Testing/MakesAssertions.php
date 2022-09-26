<?php

namespace Synthetic\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

trait MakesAssertions
{
    public function assertSet($name, $value, $strict = false)
    {
        $actual = $this->get($name);

        if (! is_string($value) && is_callable($value)) {
            PHPUnit::assertTrue($value($actual));
        } else {
            $strict ? PHPUnit::assertSame($value, $actual) : PHPUnit::assertEquals($value, $actual);
        }

        return $this;
    }

    public function assertNotSet($name, $value, $strict = false)
    {
        $actual = $this->get($name);

        $strict ? PHPUnit::assertNotSame($value, $actual) : PHPUnit::assertNotEquals($value, $actual);

        return $this;
    }
}
