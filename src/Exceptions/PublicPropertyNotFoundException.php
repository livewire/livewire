<?php

namespace Livewire\Exceptions;

class PublicPropertyNotFoundException extends \Exception
{
    use BypassViewHandler;

    public function __construct($property, $component)
    {
        return parent::__construct(
            "Unable to set component data. Public property [\${$property}] not found on component: [{$component}]"
        );
    }
}
