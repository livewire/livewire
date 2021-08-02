<?php

namespace Livewire\HydrationMiddleware;

use Closure;
use Livewire\Livewire;
use Livewire\LivewireManager;

class DisableRouteCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        ray('thing');

        $response = $next($request);

        if(LivewireManager::$shouldDisableCache){
            ray($response);
            return $response->withHeaders([
                "Pragma" => "no-cache",
                "Expires" => "Fri, 01 Jan 1990 00:00:00 GMT",
                "Cache-Control" => "no-cache, must-revalidate, no-store, max-age=0, private",
            ]);
        }

        return $response;
    }
}