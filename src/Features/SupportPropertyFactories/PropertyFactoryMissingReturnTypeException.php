<?php

namespace Livewire\Features\SupportPropertyFactories;

use Exception;

class PropertyFactoryMissingReturnTypeException extends Exception
{
    function __construct($componentName, $methodName)
    {
        parent::__construct(
            "Property factory method [{$methodName}()] must declare a return type on component: {$componentName}"
        );
    }
}
