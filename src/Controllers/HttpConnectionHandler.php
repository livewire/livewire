<?php

namespace Livewire\Controllers;

use Livewire\Livewire;
use Illuminate\Support\Facades\Request;
use Livewire\Connection\ConnectionHandler;

class HttpConnectionHandler extends ConnectionHandler
{
    public function __construct()
    {
        $this->applyPersistentMiddleware();
    }

    public function __invoke()
    {
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
        $originalUrl = Livewire::originalUrl();

        $originalRoute = app('router')->getRoutes()->match(
            Request::create($originalUrl, 'GET')
        );

        $originalRequestMiddleware = app('router')->gatherRouteMiddleware($originalRoute);

        $allowedMiddleware = Livewire::getPersistentMiddleware();

        $filteredOriginalRequestMiddleware = array_intersect($originalRequestMiddleware, $allowedMiddleware);

        $configuredMiddlewareGroup = config('livewire.middleware_group', 'web');

        $this->middleware([$configuredMiddlewareGroup, ...$filteredOriginalRequestMiddleware]);
    }
}
