<?php

namespace Livewire\Exceptions;

class CannotUseReservedLivewireComponentProperties extends \Exception
{
    use BypassViewHandler;

    public function __construct($propertyName, $componentName)
    {
        parent::__construct(
            "Public property [{$propertyName}] on [{$componentName}] component is reserved for internal Livewire use."
        );
    }
}
