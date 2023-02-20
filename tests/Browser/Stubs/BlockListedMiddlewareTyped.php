<?php

namespace Tests\Browser\Stubs;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Browser\Security\Component as SecurityComponent;

class BlockListedMiddlewareTyped
{
    public function handle(Request $request, Closure $next): Response
    {
        SecurityComponent::$loggedMiddleware[] = static::class;

        return $next($request);
    }
}
