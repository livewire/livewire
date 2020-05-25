<?php

namespace Livewire\Exceptions;

class MissingWithFileUploadsTraitException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        return parent::__construct(
            "Cannot handle file upload without [Livewire\WithFileUploads] trait on the [{$component->getName()}] component class."
        );
    }
}
