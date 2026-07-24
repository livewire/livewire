<?php

namespace Livewire\Features\SupportPropertyFactories;

use Exception;

class CannotCallPropertyFactoryDirectlyException extends Exception
{
    function __construct($componentName, $methodName)
    {
        parent::__construct(
            "Cannot call [{$methodName}()] property factory method directly on component: {$componentName}"
        );
    }
}
