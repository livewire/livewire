<?php

namespace Livewire;

use ReflectionClass;
use Illuminate\Support\Str;
use Livewire\Drawer\ImplicitlyBoundMethod;

function str($string = null)
{
    if (is_null($string)) return new class {
        public function __call($method, $params) {
            return Str::$method(...$params);
        }
    };

    return Str::of($string);
}

function invade($obj)
{
    return new class($obj) {
        public $obj;
        public $reflected;

        public function __construct($obj)
        {
            $this->obj = $obj;
            $this->reflected = new ReflectionClass($obj);
        }

        public function &__get($name)
        {
            $property = $this->reflected->getProperty($name);

            $property->setAccessible(true);

            $value = $property->getValue($this->obj);

            return $value;
        }

        public function __set($name, $value)
        {
            $property = $this->reflected->getProperty($name);

            $property->setAccessible(true);

            $property->setValue($this->obj, $value);
        }

        public function __call($name, $params)
        {
            $method = $this->reflected->getMethod($name);

            $method->setAccessible(true);

            return $method->invoke($this->obj, ...$params);
        }
    };
}

function of(...$params)
{
    return $params;
}

function revert(&$variable)
{
    $cache = $variable;

    return function () use (&$variable, $cache) {
        $variable = $cache;
    };
}

function store($instance = null)
{
    if (! $instance) $instance = \Livewire\DataStore::getInstance();

    return new class ($instance) {
        function __construct(protected $instance) {}

        function get($key, $default = null) {
            return \Livewire\DataStore::getInstance()->get($this->instance, $key, $default);
        }

        function set($key, $value) {
            return \Livewire\DataStore::getInstance()->set($this->instance, $key, $value);
        }

        function push($key, $value, $iKey = null)
        {
            return \Livewire\DataStore::getInstance()->push($this->instance, $key, $value, $iKey);
        }

        function find($key, $iKey = null, $default = null)
        {
            return \Livewire\DataStore::getInstance()->find($this->instance, $key, $iKey, $default);
        }

        function has($key, $iKey = null)
        {
            return \Livewire\DataStore::getInstance()->has($this->instance, $key, $iKey);
        }
    };
}
