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

    public function report(): bool
    {
        return ! config('app.debug');
    }

    // In debug mode, let Laravel render the full error page.
    // In production, return a generic 419 to avoid leaking details.
    public function render($request)
    {
        if (config('app.debug')) return false;

        return response('', 419);
    }
}
