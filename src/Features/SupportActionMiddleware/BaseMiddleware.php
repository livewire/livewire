<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Features\SupportAuthorization\BaseAuthorize;

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

        $authorizeMiddleware = Arr::first($middleware, function ($m) {
            return Str::before($m, ':') === AuthorizeMiddleware::class;
        });

        if ($authorizeMiddleware) {
            $this->handleAuthorizeMiddleware($authorizeMiddleware, $parameters);

            return;
        }

        Utils::applyMiddleware(request(), $middleware);
    }

    protected function handleAuthorizeMiddleware($middleware, $parameters)
    {
        $arguments = $this->parseMiddleware($middleware);

        $ability = array_shift($arguments);

        $authorizeAttribute = new BaseAuthorize($ability, $arguments);

        $authorizeAttribute->__boot(
            $this->component,
            $this->getLevel(),
            $this->getName(),
            $this->getSubName(),
            $this->getSubTarget()
        );

        $authorizeAttribute->call($parameters);
    }

    protected function parseMiddleware($middleware)
    {
        [$name, $parameters] = array_pad(explode(':', $middleware, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return $parameters;
    }
}