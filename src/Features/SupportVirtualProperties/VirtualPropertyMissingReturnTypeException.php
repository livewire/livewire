<?php

namespace Livewire\Features\SupportVirtualProperties;

use Exception;

class VirtualPropertyMissingReturnTypeException extends Exception
{
    function __construct($componentClass, $methodName)
    {
        parent::__construct(
            "Virtual property method [{$methodName}()] must declare a return type on component: {$componentClass}"
        );
    }
}
