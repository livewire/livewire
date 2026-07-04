<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Features\SupportAuthorization\BaseAuthorize;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;

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

        // This is to ensure all resolved middleware doesnt contain a closure
        $middleware = $this->filterMiddlewareByPersistentMiddleware($middleware);

        $authorizeMiddleware = Arr::first($middleware, function ($m) {
            return is_string($m) && Str::before($m, ':') == AuthorizeMiddleware::class;
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

        // pass `null` if arguments is an empty array after array_shift
        $arguments = empty($arguments) ? null : $arguments;

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

    protected function filterMiddlewareByPersistentMiddleware($middleware)
    {
        $middleware = collect($middleware);

        $persistentMiddleware = collect(app(PersistentMiddleware::class)->getPersistentMiddleware());

        return $middleware
            ->filter(function ($value, $key) use ($persistentMiddleware) {
                return $persistentMiddleware->contains(function($iValue, $iKey) use ($value) {
                    // Some middlewares can be closures.
                    if (! is_string($value)) return false;

                    // Ensure any middleware arguments aren't included in the comparison
                    return Str::before($value, ':') == $iValue;
                });
            })
            ->values()
            ->all();
    }
}