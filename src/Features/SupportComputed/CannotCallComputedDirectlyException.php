<?php

namespace Livewire\Features\SupportComputed;

use Exception;

class CannotCallComputedDirectlyException extends Exception
{
    function __construct($componentName, $methodName)
    {
        parent::__construct(
            "Cannot call [{$methodName}()] computed property method directly on component: {$componentName}"
        );
    }
}
