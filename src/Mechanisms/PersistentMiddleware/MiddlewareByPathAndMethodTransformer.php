<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Illuminate\Support\Str;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

use function Livewire\invade;

class MiddlewareByPathAndMethodTransformer
{
    protected $path;
    protected $method;

    public function getMiddlewareFromComponentsData($componentsData)
    {
        $this->processPathAndMethodFromComponentsData($componentsData);

        $middleware = $this->getMiddlewareFromPathAndMethod();

        return $middleware;
    }

    public function addDataToContext($context, $request)
    {
        [$path, $method] = $this->getPathAndMethod($request);

        $context->addMemo('path', $path);
        $context->addMemo('method', $method);
    }

    protected function processPathAndMethodFromComponentsData($componentsData)
    {
        $path = null;
        $method = null;

        foreach ($componentsData as $component) {
            // If a component doesn't have path or method, then skip
            if (
                ! isset($component['snapshot']['memo']['path'])
                || ! isset($component['snapshot']['memo']['method'])
            ) continue;

            // Store the middleware for the first component that has it
            if (empty($path) && empty($method)) {
                $path = $component['snapshot']['memo']['path'];
                $method = $component['snapshot']['memo']['method'];

                continue;
            }

            // Check path and method from other components match and throw exception if not
            if (
                $path != $component['snapshot']['memo']['path']
                || $path != $component['snapshot']['memo']['method']
            ) {
                throw new \Exception('Something went wrong, middleware are different!');
            }
        }

        // Store for later, so it can be added to the context on dehydration.
        $this->path = $path;
        $this->method = $method;
    }

    protected function getPathAndMethod()
    {
        if (app(HandleRequests::class)->isDefinitelyLivewireRequest()) {
            return [$this->path, $this->method];
        }

        $path = request()->path();
        $method = request()->method();

        return [$path, $method];
    }

    protected function getMiddlewareFromPathAndMethod()
    {
        $originalRoute = $this->getRouteFromPathAndMethod();

        if (! $originalRoute) return;

        $originalMiddleware = app('router')->gatherRouteMiddleware($originalRoute);

        return $this->getFilteredMiddleware($originalMiddleware);
    }

    protected function getRouteFromPathAndMethod()
    {
        $routes = collect(app('router')->getRoutes()->get($this->method));

        [$fallbacks, $routes] = $routes->partition(function ($route) {
            return $route->isFallback;
        });

        return $routes->merge($fallbacks)->first(
            fn ($route) => $this->pathMatchesRoute($this->path, $route)
        );
    }

    protected function pathMatchesRoute($path, $route)
    {
        $path = rtrim($path, '/') ?: '/';

        $path = '/' . ltrim($path, '/');

        invade($route)->compileRoute();

        return preg_match($route->getCompiled()->getRegex(), rawurldecode($path));
    }

    protected function getFilteredMiddleware($middleware)
    {
        $middleware = collect($middleware);

        $persistentMiddleware = collect(app(PersistentMiddleware::class)->getPersistentMiddleware());

        return $middleware
            ->filter(function ($value, $key) use ($persistentMiddleware) {
                return $persistentMiddleware->contains(function($iValue, $iKey) use ($value) {
                    // Some middlewares can be closures.
                    if (! is_string($value)) return false;

                    return Str::before($value, ':') == $iValue;
                });
            })
            ->values()
            ->all();
    }
}
