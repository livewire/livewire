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

        $store = store($this->target);

        // Already inside an outer exception boundary — call through without
        // creating a nested one so $stopPropagation() on the outer handler
        // correctly halts further execution (e.g. computed props in lifecycle hooks).
        if ($store->get('exceptionHandlingDepth', 0) > 0) {
            return ImplicitlyBoundMethod::call(app(), [$this->target, $method], $params);
        }

        $store->set('exceptionHandlingDepth', 1);

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
            $store->set('exceptionHandlingDepth', 0);
        }
    }
}
