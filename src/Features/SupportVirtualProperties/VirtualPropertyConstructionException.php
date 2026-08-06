<?php

namespace Livewire\Features\SupportVirtualProperties;

use Exception;

class VirtualPropertyConstructionException extends Exception
{
    function __construct($componentClass, $methodName, $previous)
    {
        parent::__construct(
            "Virtual property method [{$methodName}()] on component [{$componentClass}] failed: {$previous->getMessage()}. "
            ."Virtual methods construct properties before parameters are assigned and before mount() runs, so anything set there is still unset.",
            previous: $previous,
        );
    }
}
