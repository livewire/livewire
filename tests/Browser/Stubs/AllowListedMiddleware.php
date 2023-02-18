<?php

namespace Tests\Browser\Stubs;

use Tests\Browser\Security\Component as SecurityComponent;

class AllowListedMiddleware
{
    public function handle($request, $next)
    {
        SecurityComponent::$loggedMiddleware[] = static::class;

        return $next($request);
    }
}
