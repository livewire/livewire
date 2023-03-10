<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Illuminate\Support\Str;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

class MiddlewareByPathAndMethodTransformer
{
    protected $path;
    protected $method;
    protected $fakeRequest;
    protected $originalRoute;

    public function getMiddlewareFromComponentsData($componentsData)
    {
        [$this->path, $this->method] = $this->getPathAndMethodFromComponentsData($componentsData);

        $this->fakeRequest = $this->makeFakeRequestFromPathAndMethod();

        $this->originalRoute = $this->getOriginalRouteFromFakeRequest();

        if (! $this->originalRoute) return;

        $originalMiddleware = $this->getMiddlewareFromOriginalRoute();

        return $this->filterMiddlewareByPersistentMiddleware($originalMiddleware);
    }

    public function getRequest()
    {
        return $this->fakeRequest;
    }

    public function addDataToContext($context, $request)
    {
        [$path, $method] = $this->getPathAndMethod($request);

        $context->addMemo('path', $path);
        $context->addMemo('method', $method);
    }

    protected function getPathAndMethodFromComponentsData($componentsData)
    {
        $path = null;
        $method = null;

        /**
         * Iterate through each of the components in the request to find the path
         * and method, and ensure that they're the same for each component.
         */
        foreach ($componentsData as $component) {
            // If a component doesn't have path or method, then skip
            if (
                ! isset($component['snapshot']['memo']['path'])
                || ! isset($component['snapshot']['memo']['method'])
            ) continue;

            // Store the path and method from the first component that has them
            if (empty($path) && empty($method)) {
                $path = $component['snapshot']['memo']['path'];
                $method = $component['snapshot']['memo']['method'];

                continue;
            }

            // Check path and method from other components match and throw an exception if not
            if (
                $path != $component['snapshot']['memo']['path']
                || $path != $component['snapshot']['memo']['method']
            ) {
                throw new \Exception('Something went wrong, path and method are not the same for all the components in this request,');
            }
        }
        
        return [$path, $method];
    }

    protected function makeFakeRequestFromPathAndMethod()
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

    protected function getOriginalRouteFromFakeRequest()
    {
        $route = app('router')->getRoutes()->match($this->fakeRequest);

        $this->fakeRequest->setRouteResolver(fn() => $route);

        return $route;
    }

    protected function getMiddlewareFromOriginalRoute()
    {
        return app('router')->gatherRouteMiddleware($this->originalRoute);
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

    protected function getPathAndMethod()
    {
        if (app(HandleRequests::class)->isDefinitelyLivewireRequest()) {
            return [$this->path, $this->method];
        }

        $path = request()->path();
        $method = request()->method();

        return [$path, $method];
    }
}
