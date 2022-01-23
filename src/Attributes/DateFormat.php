<?php

namespace Livewire\Attributes;

use Attribute;

#[Attribute]
class DateFormat
{
    public $format;

    public function __construct($format)
    {
        $this->format = $format;
    }
}
