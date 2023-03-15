<?php

namespace Livewire;

class Memoize
{
    protected $cache = [];

    function __construct(public $target) {}

    function __call($method, $params)
    {
        return $this->returnPreviousOr($method, $params,
            fn () => invade($this->target)->$method(...$params),
        );
    }

    function returnPreviousOr($method, $params, $or)
    {
        $signature = crc32(json_encode([ $method, $params ]));

        return $this->cache[$signature] ??= $or();
    }
}
