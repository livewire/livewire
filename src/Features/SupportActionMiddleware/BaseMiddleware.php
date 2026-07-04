<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Illuminate\Support\Str;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Features\SupportRedirects\SupportRedirects;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseMiddleware extends LivewireAttribute
{
    public function __construct(public string $middleware)
    {
        //
    }

    public function call(array $parameters)
    {
        $middleware = app('router')->resolveMiddleware([$this->middleware]);

        if ($middleware === []) return;

        $middleware = $this->filterMiddleware($middleware);

        try {
            Utils::applyMiddleware(request(), $middleware);
        } catch (\Throwable $e) {
            $this->restoreOriginalRedirector();

            throw $e;
        }
    }   

    protected function restoreOriginalRedirector()
    {
        $redirectorCacheStack = SupportRedirects::$redirectorCacheStack;

        if ($redirectorCacheStack === []) return;

        $lastIndex = array_key_last($redirectorCacheStack);

        $cachedRedirector = $redirectorCacheStack[$lastIndex];

        if (is_object($cachedRedirector)) {
            app()->instance('redirect', $cachedRedirector);
        }
    }

    protected function filterMiddleware($middlewares)
    {
        $resolved = [];
        foreach ($middlewares as $middleware) {
            if (! is_string($middleware)) continue;

            if (Str::before($middleware, ':') == AuthorizeMiddleware::class) {
                throw new \InvalidArgumentException("Cannot use authorization middleware as argument");
            }

            $resolved[] = $middleware;
        }

        return $resolved;
    }
}