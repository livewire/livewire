<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use function Livewire\on;
use Illuminate\Http\Response;
use Illuminate\Pipeline\Pipeline;

class PersistentMiddleware
{
    protected static $persistentMiddleware = [
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
        app()->singleton($this::class, fn () => $this);

        on('dehydrate', function ($component, $context) {
            [$path, $method] = $this->extractPathAndMethodFromRequest($request);

            $context->addMemo('path', $path);
            $context->addMemo('method', $method);
        });

        on('yope', function ($snapshot) {
            [ $path, $method ] = $this->extractPathAndMethodFromSnapshot($snapshot);

            $this->applyPersistantMiddleware($path, $method);
        });
    }

    function addPersistentMiddleware($middleware)
    {
        static::$persistentMiddleware = array_merge(static::$persistentMiddleware, (array) $middleware);
    }

    function setPersistentMiddleware($middleware)
    {
        static::$persistentMiddleware = (array) $middleware;
    }

    function getPersistentMiddleware()
    {
        return static::$persistentMiddleware;
    }

    function runMiddleware($component)
    {
        $middleware = $this->middlewareTransformer->getMiddlewareFromComponentsData($componentsData);

        // Only send through pipeline if there are middleware found
        if (is_null($middleware)) return;

        $request = $this->middlewareTransformer->getRequest();

        return (new Pipeline(app()))
            ->send($request)
            ->through($middleware)
            ->then(function() {
                return new Response();
            });
    }
}
