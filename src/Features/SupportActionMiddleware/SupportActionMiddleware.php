<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Exceptions\EventHandlerDoesNotExist;
use Livewire\Features\SupportEvents\SupportEvents;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;

use function Livewire\invade;
use function Livewire\on;

class SupportActionMiddleware extends ComponentHook
{
    protected static $middlewareAttributes = [];

    public static function provide()
    {
        on('flush-state', function () {
            static::$middlewareAttributes = [];
        });

        on('call', function ($component, $method, $params, $context, $earlyReturn, $metadata) {
            static::applyActionMiddleware($component, $method, $params);
        });
    }

    function skip()
    {
        return empty($this->middlewareAttributes());
    }

    function boot()
    {
        if (empty(static::$middlewareAttributes)) {
            static::$middlewareAttributes = $this->middlewareAttributes();
        }
    }

    protected function middlewareAttributes(): array
    {
        return $this->component
            ->getAttributes()
            ->filter(fn ($attr) => $attr instanceof BaseMiddleware)
            ->groupBy(fn ($attr) => $attr->getName())
            ->map(fn ($group) => $group->pluck('middleware')->all())
            ->all();
    }

    protected static function applyActionMiddleware($component, $method, $params)
    {
        if (empty(static::$middlewareAttributes)) return;

        $method = static::resolveMethodName($component, $method, $params);

        $actionMiddleware = static::gatherActionMiddleware($method);

        if (empty($actionMiddleware)) return;

        [$request, $resolved] = static::resolveMiddleware($actionMiddleware);

        if (empty($resolved)) return;

        // Gather all action middleware from method and apply it all at once
        Utils::applyMiddleware($request, $resolved);
    }

    protected static function resolveMethodName($component, $method, $params)
    {
        if ($method === '__dispatch') {
            [$name, $params] = $params;

            $names = SupportEvents::getListenerEventNames($component);

            if (! in_array($name, $names)) {
                throw new EventHandlerDoesNotExist($name);
            }

            return SupportEvents::getListenerMethodName($component, $name);
        }

        return $method;
    }

    protected static function gatherActionMiddleware($method): array
    {
        return collect(static::$middlewareAttributes)
            ->filter(fn ($value, $key) => $key === $method)
            ->flatten()
            ->values()
            ->all();
    }

    protected static function resolveMiddleware(array $middleware): array
    {
        $mechanism = invade(app(PersistentMiddleware::class));

        $request = $mechanism->makeFakeRequest();

        $applicableMiddleware = $mechanism->getApplicablePersistentMiddleware($request);

        // Since PersistentMiddleware runs first, we need to exclude any middleware
        // that has been applied from it along with authorization middleware
        $resolved = collect(app('router')->resolveMiddleware($middleware, $applicableMiddleware))
            ->filter(fn ($m) => is_string($m))
            ->reject(fn ($m) => str_starts_with($m, AuthorizeMiddleware::class))
            ->values()
            ->all();

        return [$request, $resolved];
    }
}