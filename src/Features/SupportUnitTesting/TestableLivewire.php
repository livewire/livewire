<?php

namespace Livewire\Features\SupportUnitTesting;

use PHPUnit\Framework\Assert as PHPUnit;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Arr;
use Livewire\Mechanisms\ComponentDataStore;

class TestableLivewire
{
    use Macroable { __call as macroCall; }

    function __construct(
        public $instance
    ) {}

    function html()
    {
        return ComponentDataStore::get($this->instance, 'testing.html');
    }

    public function id()
    {
        //
    }

    /**
     * @todo: add in assertions...
     */

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

    function stripOutInitialData($html)
    {
        $html = preg_replace('/((?:[\n\s+]+)?wire:initial-data=\".+}"\n?|(?:[\n\s+]+)?wire:id=\"[^"]*"\n?)/m', '', $html);

        return $html;
    }


    public function instance()
    {
        //
    }

    public function viewData($key)
    {
        //
    }

    public function get($property)
    {
        //
    }

    public function emit($event, ...$parameters)
    {
        //
    }

    public function fireEvent($event, ...$parameters)
    {
        //
    }

    public function call($method, ...$parameters)
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

    public function set($name, $value = null)
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

    public function __get($property)
    {
        //
    }

    public function __call($method, $params)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }

        //

        return $this;
    }
}
