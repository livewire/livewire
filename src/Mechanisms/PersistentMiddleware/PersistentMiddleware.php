<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Illuminate\Pipeline\Pipeline;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

use function Livewire\on;

class PersistentMiddleware
{
    protected $persistentMiddleware = [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \Laravel\Jetstream\Http\Middleware\AuthenticateSession::class,
        \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\RedirectIfAuthenticated::class,
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Auth\Middleware\Authorize::class,
        \App\Http\Middleware\Authenticate::class,
    ];

    protected $componentMiddleware = [];

    function boot()
    {
        app()->singleton($this::class);

        on('dehydrate', function($component, $context) {
            $middleware = app($this::class)->getFilteredMiddlewareIndexes(request());

            $context->addMemo('middleware', $middleware);
        });

        on('flush-state', app($this::class)->flushState(...));
    }

    function addPersistentMiddleware($middleware)
    {
        $this->persistentMiddleware = array_merge($this->persistentMiddleware, (array) $middleware);
    }

    function setPersistentMiddleware($middleware)
    {
        $this->persistentMiddleware = (array) $middleware;
    }

    function getPersistentMiddleware()
    {
        return $this->persistentMiddleware;
    }

    function runRequestThroughMiddleware($request, $components, $handle)
    {
        // Assign to class property so it can be used in dehydration and dynamic child components
        $this->componentMiddleware = $this->getMiddlewareFromComponentsData($components);

        // Only send through pipeline if there are middleware found
        if (is_null($this->componentMiddleware)) return $handle($components);

        return (new Pipeline(app()))
            ->send($request)
            ->through($this->componentMiddleware)
            ->then(function() use ($handle, $components) {
                return $handle($components);
            });
    }

    function flushState()
    {
        $this->componentMiddleware = [];
    }

    protected function getFilteredMiddlewareIndexes($request)
    {
        $middleware = $this->componentMiddleware;

        if (empty($middleware)) {
            $middleware = $this->getInitialRouteMiddleware($request);
        }

        return $this->convertMiddlewareToIndexes($middleware);
    }

    protected function getInitialRouteMiddleware($request)
    {
        if (app(HandleRequests::class)->isDefinitelyLivewireRequest()) return [];

        $initialRoute = $request->route();

        return $initialRoute ? $initialRoute->middleware() : [];
    }

    protected function getMiddlewareFromComponentsData($components)
    {
        $firstGroupOfMiddleware = [];

        foreach ($components as $component) {
            // If a component doesn't have middleware, then skip
            if (! isset($component['snapshot']['memo']['middleware'])) continue;

            // Store the middleware for the first component that has it
            if (empty($firstGroupOfMiddleware)) {
                $firstGroupOfMiddleware = $component['snapshot']['memo']['middleware'];
                
                continue;
            }

            // Check middleware from other components match and throw exception if not
            if ($firstGroupOfMiddleware != $component['snapshot']['memo']['middleware']) {
                throw new \Exception('Something went wrong, middleware are different!');
            }
        }

        return $this->convertIndexesToMiddleware($firstGroupOfMiddleware);
    }

    protected function convertMiddlewareToIndexes($middleware) {
        $middleware = collect($middleware);

        $persistentMiddleware = collect($this->getPersistentMiddleware());

        return $persistentMiddleware
            ->filter(function ($value, $key) use ($middleware) {
                return $middleware->contains($value);
            })
            ->keys()
            ->all();
    }

    protected function convertIndexesToMiddleware($middlewareIndexes) {
        $middlewareIndexes = collect($middlewareIndexes);
        
        $persistentMiddleware = collect($this->getPersistentMiddleware());

        return $persistentMiddleware
            ->filter(function($value, $key) use ($middlewareIndexes) {
                return $middlewareIndexes->contains($key);
            })
            ->values()
            ->all();
    }
}
