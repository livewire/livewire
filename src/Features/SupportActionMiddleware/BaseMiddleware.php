<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
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

    public function call()
    {
        // Restore Laravel's redirector back into container
        app()->instance('redirect', array_pop(SupportRedirects::$redirectorCacheStack));

        $middleware = $this->parseMiddleware($this->middleware);
        
        if (is_null($middleware)) return;

        Utils::applyMiddleware(request(), [$middleware]);
    }

    protected function parseMiddleware($middleware)
    {
        if (class_exists($middleware)) {
            return $middleware;
        }

        // Using `getMiddleware()` to get middleware aliases as array keys
        $routeMiddleware = app('router')->getMiddleware();

        $name = Str::before($middleware, ':');

        if (! isset($routeMiddleware[$name])) {
            return null;
        }

        if (str_contains($middleware, ':')) {
            return $routeMiddleware[$name] . ':' . Str::after($middleware, ':');
        }

        return $routeMiddleware[$name];
    }
}