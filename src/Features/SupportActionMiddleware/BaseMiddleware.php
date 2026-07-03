<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Illuminate\Support\Arr;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseMiddleware extends LivewireAttribute
{
    public function __construct(public string $middleware)
    {
        //
    }

    public function boot()
    {
        store($this->component)->push(
            'middlewareFromAttributes',
            $this->getName() ?? '$refresh',
            $this->middleware
        );
    }
}