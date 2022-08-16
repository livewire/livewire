<?php

namespace Livewire\Exceptions;

class PropertyNotFoundException extends \Exception
{
    use BypassViewHandler;

    public function __construct($property, $component)
    {
        parent::__construct(
            "Property [\${$property}] not found on component: [{$component}]"
        );
    }
}
