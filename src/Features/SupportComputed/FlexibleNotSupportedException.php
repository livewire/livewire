<?php

namespace Livewire\Features\SupportComputed;

use Exception;

class FlexibleNotSupportedException extends Exception
{
    function __construct($componentName, $methodName)
    {
        parent::__construct(
            "Your Laravel version or cache driver does not support flexible cache. Seen on computed property method [{$methodName}()] on component: {$componentName}"
        );
    }
}
