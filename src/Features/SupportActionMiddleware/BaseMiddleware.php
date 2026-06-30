<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Illuminate\Support\Arr;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseMiddleware extends LivewireAttribute
{
    public function __construct(public array|string $middleware)
    {
        //
    }

    public function call()
    {
        $middleware = app('router')->resolveMiddleware(Arr::wrap($this->middleware));

        if ($middleware === []) return;

        Utils::applyMiddleware(request(), $middleware);
    }
}