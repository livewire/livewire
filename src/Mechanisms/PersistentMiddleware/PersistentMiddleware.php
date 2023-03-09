<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use function Livewire\on;
use Illuminate\Pipeline\Pipeline;

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

    protected $middlewareTransformer;

    function boot()
    {
        app()->singleton($this::class, fn() => $this);

        $this->middlewareTransformer = new MiddlewareByPathAndMethodTransformer;

        on('dehydrate', function($component, $context) {
            $this->middlewareTransformer->addDataToContext($context, request());
        });

        on('flush-state', $this->flushState(...));
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

    function runRequestThroughMiddleware($request, $componentsData, $handle)
    {
        // Assign to class property so it can be used in dehydration and dynamic child components
        $middleware = $this->middlewareTransformer->getMiddlewareFromComponentsData($componentsData);

        // Only send through pipeline if there are middleware found
        if (is_null($middleware)) return $handle($componentsData);

        return (new Pipeline(app()))
            ->send($request)
            ->through($middleware)
            ->then(function() use ($handle, $componentsData) {
                return $handle($componentsData);
            });
    }

    function flushState()
    {
        $this->middlewareTransformer = null;
    }
}
