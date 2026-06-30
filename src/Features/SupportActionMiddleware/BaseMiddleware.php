<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Illuminate\Support\Arr;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseMiddleware extends LivewireAttribute
{
    public function __construct(public string $middleware)
    {
        //
    }

    public function call()
    {
        // Remove authorization middleware as we already have `#[Authorize]` attribute
        $argument = array_filter(Arr::wrap($this->middleware), function ($m) {
            return ! str_contains($m, 'can:') && $m !== AuthorizeMiddleware::class;
        });

        $middleware = app('router')->resolveMiddleware($argument);

        if ($middleware === []) return;

        Utils::applyMiddleware(request(), $middleware);
    }
}