<?php

namespace Livewire;

class Wrapped
{
    protected $fallback;
    protected static $wrapping = [];

    function __construct(public $target) {}

    function withFallback($fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    function __call($method, $params)
    {
        if (! method_exists($this->target, $method)) return value($this->fallback);

        // Already inside a boundary for this component — run transparently
        // so the outer wrap() remains the single exception handler.
        if (in_array($this->target, static::$wrapping, true)) {
            return ImplicitlyBoundMethod::call(app(), [$this->target, $method], $params);
        }

        static::$wrapping[] = $this->target;

        try {
            return ImplicitlyBoundMethod::call(app(), [$this->target, $method], $params);
        } catch (\Throwable $e) {
            $shouldPropagate = true;

            $stopPropagation = function () use (&$shouldPropagate) {
                $shouldPropagate = false;
            };

            trigger('exception', $this->target, $e, $stopPropagation);

            $shouldPropagate && throw $e;
        } finally {
            array_pop(static::$wrapping);
        }
    }
}
