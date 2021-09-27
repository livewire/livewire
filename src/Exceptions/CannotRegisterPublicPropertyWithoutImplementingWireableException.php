<?php

namespace Livewire\Exceptions;

class CannotRegisterPublicPropertyWithoutImplementingWireableException extends \Exception
{
    public function __construct($class)
    {
        parent::__construct(
            "Cannot register the class to hydraate and dehydrate [{$class}] porperties as it's not implementing the Wireable Interface."
        );
    }
}
