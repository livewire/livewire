<?php

namespace Livewire\Exceptions;

class EventHandlerDoesNotExist extends \Exception
{
    public function __construct(public readonly string $eventName)
    {
        parent::__construct('Handler for event ' . $eventName . ' does not exist');
    }
}
