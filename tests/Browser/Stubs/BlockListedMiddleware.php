<?php

namespace Tests\Browser\Stubs;

use Tests\Browser\Security\Component as SecurityComponent;

class BlockListedMiddleware
{
    public function handle($request, $next)
    {
        SecurityComponent::$loggedMiddleware[] = static::class;

        return $next($request);
    }
}
