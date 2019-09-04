<?php

namespace Livewire\Exceptions;

class CorruptComponentPayloadException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Livewire encountered corrupt data when trying to hydrate the [{$component}] component. \n".
            "Ensure that the [name, id, data] of the Livewire component wasn't tampered with between requests."
        );
    }
}
