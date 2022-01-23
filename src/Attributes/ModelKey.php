<?php

namespace Livewire\Attributes;

use Attribute;

#[Attribute]
class ModelKey
{
    public $key;
    public $label;
    public $strict;

    public function __construct($key, $label = null, $strict = false)
    {
        $this->key = $key;
        $this->label = $label;
        $this->strict = $strict;
    }
}
