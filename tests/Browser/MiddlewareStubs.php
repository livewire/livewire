<?php

namespace Tests\Browser;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Browser\Security\Component as SecurityComponent;

if (version_compare(PHP_VERSION, '7.4', '>=')) {
    class AllowListedMiddleware
    {
        public function handle(Request $request, Closure $next): Response
        {
            SecurityComponent::$loggedMiddleware[] = static::class;

            return $next($request);
        }
    }

    class BlockListedMiddleware
    {
        public function handle(Request $request, Closure $next): Response
        {
            SecurityComponent::$loggedMiddleware[] = static::class;

            return $next($request);
        }
    }
} else {
    class AllowListedMiddleware
    {
        public function handle($request, $next)
        {
            SecurityComponent::$loggedMiddleware[] = static::class;

            return $next($request);
        }
    }

    class BlockListedMiddleware
    {
        public function handle($request, $next)
        {
            SecurityComponent::$loggedMiddleware[] = static::class;

            return $next($request);
        }
    }
}