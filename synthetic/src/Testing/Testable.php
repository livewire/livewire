<?php

namespace Synthetic\Testing;

use Synthetic\Utils;
use Illuminate\Support\Traits\Macroable;

class Testable
{
    public $target;
    public $methods;
    public $effects;
    public $snapshot;
    public $canonical;

    use MakesAssertions;

    use Macroable { __call as macroCall; }

    function __construct($dehydrated, $target) {
        $this->target = $target;
        $this->methods = $dehydrated['effects']['methods'] ?? [];
        $this->effects = $dehydrated['effects'][''];
        $this->snapshot = $dehydrated['snapshot'];
        $this->canonical = $this->extractData($this->snapshot['data']);
    }

    function get($key)
    {
        return data_get($this->target, $key);
    }

    function set($key, $value)
    {
        $dehydrated = app('synthetic')->update($this->snapshot, [$key => $value], $calls = []);

        $this->target = $dehydrated['target'];
        $this->effects = $dehydrated['effects'][''];
        $this->snapshot = $dehydrated['snapshot'];
        $this->canonical = $this->extractData($this->snapshot['data']);

        return $this;
    }

    function commit()
    {
        $dehydrated = app('synthetic')->update($this->snapshot, $diff = [], $calls = []);

        $this->target = $dehydrated['target'];
        $this->effects = $dehydrated['effects'][''];
        $this->snapshot = $dehydrated['snapshot'];
        $this->canonical = $this->extractData($this->snapshot['data']);

        return $this;
    }

    function runAction($method, ...$params)
    {
        return $this->call($method, ...$params);
    }

    function call($method, ...$params)
    {
        $dehydrated = app('synthetic')->update($this->snapshot, $diff = [], $calls = [[
            'method' => $method,
            'params' => $params,
            'path' => '',
        ]]);

        $this->target = $dehydrated['target'];
        $this->effects = $dehydrated['effects'][''];
        $this->snapshot = $dehydrated['snapshot'];
        $this->canonical = $this->extractData($this->snapshot['data']);

        return $this;
    }

    function extractData($payload) {
        $value = Utils::isSyntheticTuple($payload) ? $payload[0] : $payload;

        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $value[$key] = $this->extractData($child);
            }
        }

        return $value;
    }

    function __get($property)
    {
        return $this->target->$property;
    }

    function __set($property, $value)
    {
        throw new \Exception('Properties of this object are "readonly"');
    }
}
