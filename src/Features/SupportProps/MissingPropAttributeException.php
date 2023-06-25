<?php

namespace Livewire\Features\SupportProps;

use Exception;
use Livewire\Component;

class MissingPropAttributeException extends Exception
{
    function __construct(Component $component, $propertyName)
    {
        $name = $component->getName();

        parent::__construct(
            "Component ('{$name}') missing #[Prop] attribute on property: \${$propertyName}"
        );
    }
}
