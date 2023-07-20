<?php

namespace Livewire;

class Wrapped
{
    protected $fallback;

    function __construct(public $target) {}

    function withFallback($fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    function __call($method, $params)
    {
        if (! method_exists($this->target, $method)) return value($this->fallback);

        try {
            return ImplicitlyBoundMethod::call(app(), [$this->target, $method], $params);
        } catch (\Throwable $e) {
            $shouldPropagate = true;

            $stopPropagation = function () use (&$shouldPropagate) {
                $shouldPropagate = false;
            };

            trigger('exception', $this->target, $e, $stopPropagation);

            $shouldPropagate && throw $e;
        }
    }
}




