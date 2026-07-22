<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseMiddleware extends LivewireAttribute
{
    public function __construct(public string $middleware)
    {
        //
    }

    function boot()
    {
        $store = store($this->component);

        $middleware = $store->find('middlewareAttributes', $this->getName(), []);

        $middleware[] = $this->middleware;

        $store->push('middlewareAttributes', $middleware, $this->getName());
    }
}