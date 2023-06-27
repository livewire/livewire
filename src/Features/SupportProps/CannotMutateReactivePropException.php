<?php

namespace Livewire\Features\SupportProps;

use Exception;

class CannotMutateReactivePropException extends Exception
{
    function __construct($componentName, $propName)
    {
        parent::__construct("Cannot mutate reactive prop [{$propName}] in component: [{$componentName}]");
    }
}
