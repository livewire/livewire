<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

use function Livewire\on;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseMiddleware extends LivewireAttribute
{
    protected $middlewareFromAttributes = [];

    public function __construct(public string $middleware)
    {
        //
    }

    public function boot()
    {
        on('flush-state', function () {
            $this->middlewareFromAttributes = [];
        });

        // Only gather middleware attributes if request hitting Livewire update endpoint
        // following how persistent middleware applied
        if (app(HandleRequests::class)->isLivewireRoute()) {
            $this->middlewareFromAttributes = $this->component
                ->getAttributes()
                ->filter(fn ($attr) => $attr instanceof BaseMiddleware)
                ->filter(fn ($attr) => $attr->getName() === $this->getName())
                ->map(fn ($attr) => $attr->middleware)
                ->values()
                ->all();
        }
    }

    function call()
    {
        if (empty($this->middlewareFromAttributes)) return;

        if (empty($actionMiddleware = $this->resolveMiddleware())) return;

        Utils::applyMiddleware(request(), $actionMiddleware);
    }

    protected function resolveMiddleware()
    {
        return collect(app('router')->resolveMiddleware($this->middlewareFromAttributes))
            ->filter(fn ($m) => is_string($m))
            // Exclude any authorization middleware since we already
            // have `#[Authorize]` attribute
            ->reject(fn ($m) => str_starts_with($m, AuthorizeMiddleware::class))
            ->values()
            ->all();
    }
}