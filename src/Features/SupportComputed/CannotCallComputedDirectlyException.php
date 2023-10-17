<?php

namespace Livewire\Features\SupportComputed;

use Exception;

class CannotCallComputedDirectlyException extends Exception
{
    public function __construct($componentName, $methodName)
    {
        parent::__construct(
            "Cannot call [{$methodName}()] computed property method directly on component: {$componentName}"
        );
    }
}
