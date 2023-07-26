<?php

namespace Livewire\Exceptions;

class ResetPropertyNotAllowed extends \Exception
{
    use BypassViewHandler;

    public function __construct($property)
    {
        parent::__construct(
            "Property not allowed to be reset: [{$property}]."
        );
    }
}
