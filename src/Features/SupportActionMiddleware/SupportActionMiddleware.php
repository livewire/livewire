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
    public static function provide()
    {
        on('call', function ($component, $method, $params, $context, $earlyReturn, $metadata) {
            $method = static::resolveMethodName($component, $method, $params);

            $actionMiddleware = static::gatherActionMiddleware($component, $method);

            if (empty($actionMiddleware)) return;

            [$request, $resolved] = static::resolveMiddleware($actionMiddleware);

            if (empty($resolved)) return;

            // Gather all action middleware from method and apply it all at once
            Utils::applyMiddleware($request, $resolved);
        });
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

    protected static function gatherActionMiddleware($component, $method): array
    {
        return $component->getAttributes()
            ->filter(fn ($attr) => $attr instanceof BaseMiddleware)
            ->filter(fn ($attr) => $attr->getName() === $method)
            ->map(fn ($attr) => $attr->middleware)
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