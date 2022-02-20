<?php

namespace Livewire;

class Property
{
    public $get;

    public function __construct(callable $get = null)
    {
        $this->get = $get;
    }

    public static function get(callable $get)
    {
        return new static($get);
    }
}
