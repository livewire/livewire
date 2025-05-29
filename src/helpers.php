<?php

namespace Livewire;

use ReflectionClass;
use Illuminate\Support\Str;

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
            $getProperty = function &() use ($name) {
                return $this->{$name};
            };

            $getProperty = $getProperty->bindTo($this->obj, get_class($this->obj));

            return $getProperty();
        }

        public function __set($name, $value)
        {
            $setProperty = function () use ($name, &$value) {
                $this->{$name} = $value;
            };

            $setProperty = $setProperty->bindTo($this->obj, get_class($this->obj));

            $setProperty();
        }

        public function __call($name, $params)
        {
            $method = $this->reflected->getMethod($name);

            return $method->invoke($this->obj, ...$params);
        }
    };
}

function once($fn)
{
    $hasRun = false;

    return function (...$params) use ($fn, &$hasRun) {
        if ($hasRun) return;

        $hasRun = true;

        return $fn(...$params);
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

function wrap($subject) {
    return new Wrapped($subject);
}

function pipe($subject) {
    return new Pipe($subject);
}

function trigger($name, ...$params) {
    return app(\Livewire\EventBus::class)->trigger($name, ...$params);
}

function on($name, $callback) {
    return app(\Livewire\EventBus::class)->on($name, $callback);
}

function after($name, $callback) {
    return app(\Livewire\EventBus::class)->after($name, $callback);
}

function before($name, $callback) {
    return app(\Livewire\EventBus::class)->before($name, $callback);
}

function off($name, $callback) {
    app(\Livewire\EventBus::class)->off($name, $callback);
}

function memoize($target) {
    static $memo = new \WeakMap;

    return new class ($target, $memo) {
        function __construct(
            protected $target,
            protected &$memo,
        ) {}

        function __call($method, $params)
        {
            $this->memo[$this->target] ??= [];

            $signature = $method . crc32(json_encode($params));

            return $this->memo[$this->target][$signature]
               ??= $this->target->$method(...$params);
        }
    };
}

function store($instance = null)
{
    if (! $instance) $instance = app(\Livewire\Mechanisms\DataStore::class);

    return new class ($instance) {
        function __construct(protected $instance) {}

        function get($key, $default = null) {
            return app(\Livewire\Mechanisms\DataStore::class)->get($this->instance, $key, $default);
        }

        function set($key, $value) {
            return app(\Livewire\Mechanisms\DataStore::class)->set($this->instance, $key, $value);
        }

        function push($key, $value, $iKey = null)
        {
            return app(\Livewire\Mechanisms\DataStore::class)->push($this->instance, $key, $value, $iKey);
        }

        function find($key, $iKey = null, $default = null)
        {
            return app(\Livewire\Mechanisms\DataStore::class)->find($this->instance, $key, $iKey, $default);
        }

        function has($key, $iKey = null)
        {
            return app(\Livewire\Mechanisms\DataStore::class)->has($this->instance, $key, $iKey);
        }

        function unset($key, $iKey = null)
        {
            return app(\Livewire\Mechanisms\DataStore::class)->unset($this->instance, $key, $iKey);
        }
    };
}
