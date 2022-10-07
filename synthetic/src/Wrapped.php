<?php

namespace Synthetic;

class Wrapped
{
    function __construct(public $target) {}

    function __call($method, $params)
    {
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




