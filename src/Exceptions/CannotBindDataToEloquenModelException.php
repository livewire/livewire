<?php

namespace Livewire\Exceptions;

class CannotBindDataToEloquenModelException extends \Exception
{
    use BypassViewHandler;

    public function __construct($propertyName)
    {
        parent::__construct(
            "Cannot bind Eloquent model using [wire:model=\"$propertyName\"]."
        );
    }
}
