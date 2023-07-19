<?php

namespace Livewire\Features\SupportFileUploads;

use Exception;
use Livewire\Exceptions\BypassViewHandler;

class MissingFileUploadsTraitException extends Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Cannot handle file upload without [Livewire\WithFileUploads] trait on the [{$component->getName()}] component class."
        );
    }
}
