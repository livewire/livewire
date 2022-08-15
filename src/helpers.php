<?php

namespace Livewire;

use ReflectionClass;
use Illuminate\Support\Str;

if (! function_exists('Livewire\str')) {
    function str($string = null)
    {
        if (is_null($string)) return new class {
            public function __call($method, $params) {
                return Str::$method(...$params);
            }
        };

        return Str::of($string);
    }
}

if (! function_exists('Livewire\invade')) {
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

            public function __get($name)
            {
                $property = $this->reflected->getProperty($name);

                $property->setAccessible(true);

                return $property->getValue($this->obj);
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
}
