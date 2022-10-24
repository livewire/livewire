<?php

namespace Livewire\Features\SupportUnitTesting;

use Synthetic\Testing\Testable as BaseTestable;
use Synthetic\TestableSynthetic;
use PHPUnit\Framework\Assert as PHPUnit;
use Livewire\Mechanisms\ComponentDataStore;
use Livewire\Features\SupportValidation\TestsValidation;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use Livewire\Features\SupportEvents\TestsEvents;

class Testable extends BaseTestable
{
    use MakesAssertions, TestsValidation, TestsEvents;

    function html()
    {
        return componentdatastore::get($this->target, 'testing.html');
    }

    function view()
    {
        return componentdatastore::get($this->target, 'testing.view');
    }

    public function id()
    {
        return $this->target->id();
    }

    public function instance()
    {
        return $this->target;
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

        if ($method === '$set') {
            return parent::set(...$params);
        }

        return parent::call($method, ...$params);
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
