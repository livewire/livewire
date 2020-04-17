<?php

namespace Livewire\Exceptions;

class InvalidComponentMountStateException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Livewire encountered a missing mount method when trying to initialise the [{$component}] " .
            "component. \n When passing non-numeric keyed arrays to components ensure you have a mount method " .
            "on the component."
        );
    }
}
