<?php

namespace Livewire\Exceptions;

class CannotRegisterPublicPropertyWithoutImplementingWireableException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            "Cannot register the class to hydraate and dehydrate properties as it's not implementing the Wireable Interface."
        );
    }
}
