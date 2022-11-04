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

    public function assertCount($name, $value)
    {
        PHPUnit::assertCount($value, $this->get($name));

        return $this;
    }

    public function assertSnapshotSet($name, $value, $strict = false)
    {
        $data = $this->extractData($this->snapshot)['data'];

        if (is_callable($value)) {
            PHPUnit::assertTrue($value(data_get($data, $name)));
        } else {
            $strict ? PHPUnit::assertSame($value, data_get($data, $name)) : PHPUnit::assertEquals($value, data_get($data, $name));
        }

        return $this;
    }

    public function assertSnapshotNotSet($name, $value, $strict = false)
    {
        $data = $this->extractData($this->snapshot)['data'];

        if (is_callable($value)) {
            PHPUnit::assertFalse($value(data_get($data, $name)));
        } else {
            $strict ? PHPUnit::assertNotSame($value, data_get($data, $name)) : PHPUnit::assertNotEquals($value, data_get($data, $name));
        }

        return $this;
    }
}
