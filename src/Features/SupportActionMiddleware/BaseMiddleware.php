<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Illuminate\Support\Str;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseMiddleware extends LivewireAttribute
{
    public function __construct(public string $middleware)
    {
        //
    }

    function call(array $parameters)
    {
        $resolvedMiddleware = app('router')->resolveMiddleware([$this->middleware]);

        if ($resolvedMiddleware === []) return;

        $middleware = $this->filterMiddleware($resolvedMiddleware);

        Utils::applyMiddleware(request(), $middleware);
    }

    protected function filterMiddleware(array $resolvedMiddleware)
    {
        $filtered = [];
        foreach ($resolvedMiddleware as $middleware) {
            if (! is_string($middleware)) continue;

            if (Str::before($middleware, ':') == AuthorizeMiddleware::class) continue;

            $filtered[] = $middleware;
        }

        return $filtered;
    }
}