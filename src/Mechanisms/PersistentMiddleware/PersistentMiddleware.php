<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Router;
use Livewire\Mechanisms\Mechanism;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function Livewire\on;
use function Livewire\store;
use Illuminate\Support\Str;
use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

class PersistentMiddleware extends Mechanism
{
    protected const ROUTE_BINDING_ERROR_PAGE = 'routeBindingErrorPage';

    protected const ROUTE_BINDING_ERROR_PAGE_ATTRIBUTE = 'livewire_route_binding_error_page';

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

    protected $path;
    protected $method;
    protected $middlewareAppliedFor = [];
    protected $resolvedRouteModels = [];

    function boot()
    {
        app('view')->composer('errors::*', function ($view) {
            $exception = $view->getData()['exception'] ?? null;

            if (
                $exception instanceof NotFoundHttpException
                && $exception->getPrevious() instanceof ModelNotFoundException
            ) {
                request()->attributes->set(static::ROUTE_BINDING_ERROR_PAGE_ATTRIBUTE, true);
            }
        });

        on('mount', function ($component, $params, $key, $parent) {
            if (
                request()->attributes->get(static::ROUTE_BINDING_ERROR_PAGE_ATTRIBUTE)
                || ($parent && store($parent)->get(static::ROUTE_BINDING_ERROR_PAGE))
            ) {
                store($component)->set(static::ROUTE_BINDING_ERROR_PAGE, true);
            }
        });

        on('hydrate', function ($component, $memo) {
            if ($memo[static::ROUTE_BINDING_ERROR_PAGE] ?? false) {
                store($component)->set(static::ROUTE_BINDING_ERROR_PAGE, true);
            }
        });

        on('dehydrate', function ($component, $context) {
            // Components rendered by a route model binding 404 never made it
            // through the original route's middleware, so there is no
            // successful middleware context to replay on their updates.
            if (store($component)->get(static::ROUTE_BINDING_ERROR_PAGE)) {
                $context->addMemo(static::ROUTE_BINDING_ERROR_PAGE, true);

                return;
            }

            [$path, $method] = $this->extractPathAndMethodFromRequest();

            $context->addMemo('path', $path);
            $context->addMemo('method', $method);
        });

        on('snapshot-verified', function ($snapshot) {
            // Only apply middleware to requests hitting the Livewire update endpoint, and not any fake requests such as a test.
            if (! app(HandleRequests::class)->isLivewireRoute()) return;

            // This flag was added to the checksummed snapshot while Laravel
            // rendered a route model binding 404 response.
            if ($snapshot['memo'][static::ROUTE_BINDING_ERROR_PAGE] ?? false) return;

            $this->extractPathAndMethodFromSnapshot($snapshot);

            $this->applyPersistentMiddleware();
        });

        on('flush-state', function() {
            // Only flush these at the end of a full request, so that child components have access to this data.
            $this->path = null;
            $this->method = null;
            $this->middlewareAppliedFor = [];
            $this->resolvedRouteModels = [];
        });
    }

    function addPersistentMiddleware($middleware)
    {
        static::$persistentMiddleware = Router::uniqueMiddleware(array_merge(static::$persistentMiddleware, (array) $middleware));
    }

    function setPersistentMiddleware($middleware)
    {
        static::$persistentMiddleware = Router::uniqueMiddleware((array) $middleware);
    }

    function getPersistentMiddleware()
    {
        return static::$persistentMiddleware;
    }

    function getResolvedRouteModel($class, $key)
    {
        return $this->resolvedRouteModels[$class.':'.$key] ?? null;
    }

    protected function extractPathAndMethodFromRequest()
    {
        if (app(HandleRequests::class)->isLivewireRoute()) {
            return [$this->path, $this->method];
        }

        return [request()->path(), request()->method()];
    }

    protected function extractPathAndMethodFromSnapshot($snapshot)
    {
        if (
            ! isset($snapshot['memo']['path'])
            || ! isset($snapshot['memo']['method'])
        ) return;

        // Store these locally, so dynamically added child components can use this data.
        $this->path = $snapshot['memo']['path'];
        $this->method = $snapshot['memo']['method'];
    }

    protected function applyPersistentMiddleware()
    {
        $routeKey = $this->method . '|' . $this->path;

        // If middleware has already been applied for this route in the current
        // request cycle, skip re-applying. When multiple component snapshots
        // share the same route (e.g. parent + lazy/reactive child), this
        // prevents SubstituteBindings from re-resolving explicit route model
        // bindings with already-resolved model instances instead of raw strings.
        if (isset($this->middlewareAppliedFor[$routeKey])) {
            return;
        }

        $request = $this->makeFakeRequest();

        // If no middleware found, this returns `[]`
        $middleware = $this->getApplicablePersistentMiddleware($request);

        // Only send through pipeline if there are middleware found
        if (is_null($middleware) || $middleware === []) return;

        Utils::applyMiddleware($request, $middleware);

        $this->middlewareAppliedFor[$routeKey] = true;

        // After middleware has run (e.g. SubstituteBindings), collect any
        // resolved model instances from the route parameters so that
        // ModelSynth can reuse them instead of re-querying the database.
        if ($route = $request->route()) {
            foreach ($route->parameters() as $parameter) {
                if ($parameter instanceof Model) {
                    $key = get_class($parameter).':'.$parameter->getKey();
                    $this->resolvedRouteModels[$key] = $parameter;
                }
            }
        }
    }

    protected function makeFakeRequest()
    {
        $originalPath = $this->formatPath($this->path);
        $originalMethod = $this->method;

        $currentPath = $this->formatPath(request()->path());

        // Clone server bag to ensure changes below don't overwrite the original.
        $serverBag = clone request()->server;

        // Replace the Livewire endpoint path with the path from the original request.
        $serverBag->set(
            'REQUEST_URI',
            str_replace($currentPath, $originalPath, $serverBag->get('REQUEST_URI'))
        );

        $serverBag->set('REQUEST_METHOD', $originalMethod);

        /**
         * Make the fake request from the current request with path and method changed so
         * all other request data, such as headers, are available in the fake request,
         * but merge in the new server bag with the updated `REQUEST_URI`.
         */
        $request = request()->duplicate(
            server: $serverBag->all()
        );

        return $request;
    }

    protected function formatPath($path)
    {
        return '/' . ltrim($path, '/');
    }

    protected function getApplicablePersistentMiddleware($request)
    {
        $route = $this->getRouteFromRequest($request);

        if (! $route) return [];

        $middleware = app('router')->gatherRouteMiddleware($route);

        return $this->filterMiddlewareByPersistentMiddleware($middleware);
    }

    protected function getRouteFromRequest($request)
    {
        try {
            $route = app('router')->getRoutes()->match($request);
            $route->setContainer(app());
            $request->setRouteResolver(fn() => $route);
        } catch (NotFoundHttpException $e){
            return null;
        }

        return $route;
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
