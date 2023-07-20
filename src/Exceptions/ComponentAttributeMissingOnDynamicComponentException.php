<?php

namespace Livewire\Exceptions;

class ComponentAttributeMissingOnDynamicComponentException extends \Exception
{
    use BypassViewHandler;

    public function __construct()
    {
        parent::__construct('Dynamic component tag is missing component attribute.');
    }
}
