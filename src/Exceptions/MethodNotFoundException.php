<?php

namespace Livewire\Exceptions;

class MethodNotFoundException extends \Exception
{
    use BypassViewHandler;

    public function __construct($method)
    {
        parent::__construct(
            "Unable to call component method. Public method [{$method}] not found on component"
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
