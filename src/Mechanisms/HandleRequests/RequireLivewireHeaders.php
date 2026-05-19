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

        // Remove duplicate middleware from middleware stack
        // Without this, `RequireLivewireHeaders` will be applied twice
        if (($route = request()->route()) && isset($route->action['middleware'])) {
            $route->action['middleware'] = array_unique(array_values(
                array_filter($route->getAction('middleware'), fn ($m) => is_string($m))
            ));
        }

        return $next($request);
    }
}
