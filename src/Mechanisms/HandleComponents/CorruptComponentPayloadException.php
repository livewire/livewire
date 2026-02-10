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

    /**
     * Render the exception as an HTTP response.
     *
     * Returns 419 ("Page Expired") — the JS client already handles this status
     * with a "page expired, refresh?" prompt, which is the correct UX for stale
     * snapshots (expired session, rotated key, stale tab). A generic body avoids
     * leaking any internal details to attackers.
     */
    public function render($request)
    {
        if (config('app.debug')) return false;

        return response('', 419);
    }
}
