<?php

namespace Livewire\Mechanisms\HandleComponents;

use Livewire\Exceptions\BypassViewHandler;

class CorruptComponentPayloadException extends \Exception
{
    use BypassViewHandler;

    public function __construct()
    {
        parent::__construct(
            "Livewire encountered corrupt data when trying to hydrate a component. \n".
            "Ensure that the [name, id, data] of the Livewire component wasn't tampered with between requests."
        );
    }
}
