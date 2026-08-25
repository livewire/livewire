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

        $depth = $store->get('exceptionHandlingDepth', 0);

        $store->set('exceptionHandlingDepth', $depth + 1);

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
            $store->set(
                'exceptionHandlingDepth',
                max(0, $store->get('exceptionHandlingDepth', 0) - 1)
            );
        }
    }
}




