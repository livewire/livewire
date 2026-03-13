<?php

namespace Livewire\Exceptions;

class ModelableRootHasWireModelException extends \Exception
{
    use BypassViewHandler;

    public function __construct()
    {
        parent::__construct(
            "A #[Modelable] component's root element cannot have wire:model. " .
            "Wrap your input element in a <div> so Livewire can inject the parent binding on the root element."
        );
    }
}
