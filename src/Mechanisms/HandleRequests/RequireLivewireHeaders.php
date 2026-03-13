<?php

namespace Livewire\Mechanisms\HandleRequests;

use Closure;

class RequireLivewireHeaders
{
    function handle($request, Closure $next)
    {
        // Reject requests missing required headers. The legitimate Livewire
        // JS client always sends both X-Livewire and Content-Type: application/json.
        // Their absence indicates the request did not come from Livewire.
        // Return 404 to avoid confirming the endpoint exists to scanners.
        if (! $request->hasHeader('X-Livewire') || ! $request->isJson()) {
            abort(404);
        }

        return $next($request);
    }
}
