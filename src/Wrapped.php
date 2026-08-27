<?php

namespace Livewire;

class Wrapped
{
    protected $fallback;

    protected static $activeCalls;

    function __construct(public $target) {}

    function withFallback($fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    function __call($method, $params)
    {
        if (! method_exists($this->target, $method)) return value($this->fallback);

        static::$activeCalls ??= new \WeakMap;

        $isNested = isset(static::$activeCalls[$this->target]);

        if (! $isNested) static::$activeCalls[$this->target] = true;

        try {
            return ImplicitlyBoundMethod::call(app(), [$this->target, $method], $params);
        } catch (\Throwable $e) {
            // Let the outer wrapper handle exceptions from nested calls so that
            // stopping propagation also stops the outer method's execution.
            if ($isNested) throw $e;

            $shouldPropagate = true;

            $stopPropagation = function () use (&$shouldPropagate) {
                $shouldPropagate = false;
            };

            trigger('exception', $this->target, $e, $stopPropagation);

            $shouldPropagate && throw $e;
        } finally {
            if (! $isNested) unset(static::$activeCalls[$this->target]);
        }
    }
}



