<?php

namespace Livewire\Exceptions;

class MountMethodMissingException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Livewire encountered a missing mount method when trying to initialise the [{$component}] " .
            "component. \n When passing component parameters, make sure you have a mount method."
        );
    }
}
