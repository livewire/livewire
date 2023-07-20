<?php

namespace Livewire\Exceptions;

class MethodNotFoundException extends \Exception
{
    use BypassViewHandler;

    public function __construct($method)
    {
        parent::__construct(
            "Unable to call component method. Public method [{$method}] not found on component"
        );
    }
}
