<?php

namespace Livewire;

class Pipe implements \ArrayAccess, \IteratorAggregate, \Stringable
{
    use Transparency;

    public function __construct($target)
    {
        $this->target = $target;
    }

    public function __invoke(...$params)
    {
        if (empty($params)) {
            return $this->target;
        }

        [$before, $through, $after] = [[], null, []];

        foreach ($params as $key => $param) {
            if (! $through) {
                if (is_callable($param)) {
                    $through = $param;
                } else {
                    $before[$key] = $param;
                }
            } else {
                $after[$key] = $param;
            }
        }

        $params = [...$before, $this->target, ...$after];

        $this->target = $through(...$params);

        return $this;
    }
}
