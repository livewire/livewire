<?php

namespace Livewire\Attributes;

use Attribute;

#[Attribute]
class ModelKey
{
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}
