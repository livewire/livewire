<?php

namespace Livewire\Controllers;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Request;
use Livewire\Connection\ConnectionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HttpConnectionHandler extends ConnectionHandler
{
    public function __invoke()
    {
        $this->applyPersistentMiddleware();

        return $this->handle(
            request([
                'fingerprint',
                'serverMemo',
                'updates',
            ])
        );
    }

    public function applyPersistentMiddleware()
    {
        try {
            $originalUrl = Livewire::originalUrl();

            // If the original path was the root route, updated the original URL to have
            // a suffix of '/' to ensure that the route matching works correctly when
            // a prefix is used (such as running Laravel in a subdirectory).
            if (Livewire::originalPath() == '/') {
                $originalUrl .= '/';
            }

            $request = $this->makeRequestFromUrlAndMethod(
                $originalUrl,
                Livewire::originalMethod()
            );
        } catch (NotFoundHttpException $e) {

            $originalUrl = Str::replaceFirst('/'.request('fingerprint')['locale'], '', Livewire::originalUrl());

            // If the original path was the root route, updated the original URL to have
            // a suffix of '/' to ensure that the route matching works correctly when
            // a prefix is used (such as running Laravel in a subdirectory).
            if (Livewire::originalPath() == request('fingerprint')['locale']) {
                $originalUrl .= '/';
            }

            $request = $this->makeRequestFromUrlAndMethod(
                $originalUrl,
                Livewire::originalMethod()
            );
        }

        // Gather all the middleware for the original route, and filter it by
        // the ones we have designated for persistence on Livewire requests.
        $originalRouteMiddleware = app('router')->gatherRouteMiddleware($request->route());

        $persistentMiddleware = Livewire::getPersistentMiddleware();

        $filteredMiddleware = collect($originalRouteMiddleware)->filter(function ($middleware) use ($persistentMiddleware) {
            // Some middlewares can be closures.
            if (! is_string($middleware)) return false;

            return in_array(Str::before($middleware, ':'), $persistentMiddleware);
        })->toArray();

        // Now run the faux request through the original middleware with a custom pipeline.
        (new Pipeline(app()))
            ->send($request)
            ->through($filteredMiddleware)
            ->then(function() {
                // noop
            });
    }

    protected function makeRequestFromUrlAndMethod($url, $method = 'GET')
    {
        // Ensure the original script paths are passed into the fake request incase Laravel is running in a subdirectory
        $request = Request::create($url, $method, [], [], [], [
            'SCRIPT_NAME' => request()->server->get('SCRIPT_NAME'),
            'SCRIPT_FILENAME' => request()->server->get('SCRIPT_FILENAME'),
            'PHP_SELF' => request()->server->get('PHP_SELF'),
        ]);

        if (request()->hasSession()) {
            $request->setLaravelSession(request()->session());
        }

        $request->setUserResolver(request()->getUserResolver());

        $route = app('router')->getRoutes()->match($request);

        // For some reason without this octane breaks the route parameter binding.
        $route->setContainer(app());

        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        return $request;
    }
}
