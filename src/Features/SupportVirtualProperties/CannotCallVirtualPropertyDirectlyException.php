<?php

namespace Livewire\Features\SupportVirtualProperties;

use Exception;

class CannotCallVirtualPropertyDirectlyException extends Exception
{
    function __construct($componentName, $methodName)
    {
        parent::__construct(
            "Cannot call [{$methodName}()] virtual property method directly on component: {$componentName}"
        );
    }
}
