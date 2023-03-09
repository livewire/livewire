<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Illuminate\Support\Str;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

class MiddlewareByIndexTransformer
{
    protected $componentMiddleware;

    public function getMiddlewareFromComponentsData($componentsData)
    {
        $this->processMiddlewareFromComponentsData($componentsData);

        return $this->componentMiddleware;
    }

    public function addDataToContext($context, $request)
    {
        $middleware = $this->getFilteredMiddlewareIndexes($request);

        $context->addMemo('middleware', $middleware);
    }

    protected function processMiddlewareFromComponentsData($componentsData)
    {
        $firstGroupOfMiddleware = [];

        foreach ($componentsData as $component) {
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

        // Store for later, so it can be added to the context on dehydration.
        $this->componentMiddleware = $this->convertIndexesToMiddleware($firstGroupOfMiddleware);
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

        if (is_null($initialRoute)) return [];

        // Use Laravel to convert string middleware, such as 'auth' to classes
        return app('router')->gatherRouteMiddleware($initialRoute);
    }

    protected function convertMiddlewareToIndexes($middleware) {
        $middleware = collect($middleware);

        $persistentMiddleware = collect(app(PersistentMiddleware::class)->getPersistentMiddleware());

        return $persistentMiddleware
            ->filter(function ($value, $key) use ($middleware) {

                return $middleware->contains(function($iValue, $iKey) use ($value) {
                    $iValue = Str::before($iValue, ':');

                    if ($iValue == $value) return true;

                    if (
                        class_exists($value)
                        && class_exists($iValue)
                        && is_subclass_of($iValue, $value)
                    ) return true;

                    return false;
                });
            })
            ->keys()
            ->all();
    }

    protected function convertIndexesToMiddleware($middlewareIndexes) {
        $middlewareIndexes = collect($middlewareIndexes);

        $persistentMiddleware = collect(app(PersistentMiddleware::class)->getPersistentMiddleware());

        return $persistentMiddleware
            ->filter(function($value, $key) use ($middlewareIndexes) {
                return $middlewareIndexes->contains($key);
            })
            ->values()
            ->all();
    }
}
