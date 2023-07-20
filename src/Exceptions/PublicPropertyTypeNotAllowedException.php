<?php

namespace Livewire\Exceptions;

class PublicPropertyTypeNotAllowedException extends \Exception
{
    use BypassViewHandler;

    public function __construct($componentName, $key, $value)
    {
        parent::__construct(
            "Livewire component's [{$componentName}] public property [{$key}] must be of type: [numeric, string, array, null, or boolean].\n".
            "Only protected or private properties can be set as other types because JavaScript doesn't need to access them."
        );
    }
}
