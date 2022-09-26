<?php

namespace Livewire\Features\SupportUnitTesting;

use PHPUnit\Framework\Assert as PHPUnit;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Arr;
use Livewire\Mechanisms\ComponentDataStore;
use Synthetic\TestableSynthetic;
use Synthetic\Testing\Testable as BaseTestable;

class Testable extends BaseTestable
{
    function html()
    {
        return ComponentDataStore::get($this->target, 'testing.html');
    }

    public function id()
    {
        //
    }

    public function assertSee($values, $escape = true)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringContainsString(
                $escape ? e($value): $value,
                $this->html()
            );
        }

        return $this;
    }

    public function instance()
    {
        //
    }

    public function viewData($key)
    {
        //
    }

    function call($method, ...$params)
    {
        if ($method === '$refresh') {
            return parent::commit();
        }

        return parent::call($method, ...$params);
    }

    public function emit($event, ...$parameters)
    {
        //
    }

    public function fireEvent($event, ...$parameters)
    {
        //
    }

    public function runAction($method, ...$parameters)
    {
        //
    }

    public function fill($values)
    {
        //
    }

    public function toggle($name)
    {
        //
    }

    public function updateProperty($name, $value = null)
    {
        //
    }

    public function syncInput($name, $value)
    {
        //
    }

    public function syncUploadedFiles($name, $files, $isMultiple = false)
    {
        //
    }

    public function sendMessage($message, $payload)
    {
        //
    }

    public function dump()
    {
        //
    }

    public function tap($callback)
    {
        //
    }

}
