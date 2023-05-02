<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class DisableBackButtonCacheMiddleware
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
        $response = $next($request);

        if ($response instanceof Response && SupportDisablingBackButtonCache::$disableBackButtonCache){
            $response->headers->add([
                "Pragma" => "no-cache",
                "Expires" => "Fri, 01 Jan 1990 00:00:00 GMT",
                "Cache-Control" => "no-cache, must-revalidate, no-store, max-age=0, private",
            ]);
        }

        return $response;
    }
}
