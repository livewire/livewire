<?php

namespace Livewire\Features\SupportGetters;

use Exception;

class CannotCallGetterDirectlyException extends Exception
{
    function __construct($componentName, $methodName)
    {
        parent::__construct(
            "Cannot call [{$methodName}()] getter method directly on component: {$componentName}"
        );
    }
}
