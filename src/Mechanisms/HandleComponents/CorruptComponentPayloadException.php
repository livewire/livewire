<?php

namespace Livewire\Mechanisms\HandleComponents;

use Livewire\Exceptions\BypassViewHandler;

class CorruptComponentPayloadException extends \Exception
{
    use BypassViewHandler;

    public function __construct(?string $debugContext = null)
    {
        $message =
            "Livewire encountered corrupt data when trying to hydrate a component. \n".
            "Ensure that the [name, id, data] of the Livewire component wasn't tampered with between requests.";

        if ($debugContext) {
            $message .= "\n\n".$debugContext;
        }

        parent::__construct(
            $message
        );
    }

    // In debug mode, let Laravel render the full error page.
    // In production, return a generic 419 to avoid leaking details.
    public function render($request)
    {
        if (config('app.debug')) return false;

        return response('', 419);
    }
}
