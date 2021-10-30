<?php

namespace Livewire\Exceptions;

class CannotRegisterPublicPropertyWithoutExtendingThePropertyHandlerException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            "Please make sure your resolver class does extend the `Livewire\PropertyHandler::class`"
        );
    }
}
