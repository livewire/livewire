<?php

namespace Livewire\Features\SupportComputed;

use Exception;

class CountArrayElementsFlexibleCacheException extends Exception
{
    function __construct($componentName, $methodName)
    {
        parent::__construct(
            "Cannot add more than two array elements to a flexible computed property method [{$methodName}()] on component: {$componentName}"
        );
    }
}
