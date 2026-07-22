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
use function Livewire\store;

class SupportActionMiddleware extends ComponentHook
{
    public static function provide()
    {
        on('call', function ($component, $method, $params, $context, $earlyReturn, $metadata) {
            static::applyActionMiddleware($component, $method, $params);
        });
    }

    protected static function applyActionMiddleware($component, $method, $params)
    {
        $method = static::resolveMethodName($component, $method, $params);

        // Return early if there is no middleware attribute on called method
        if (! $actionMiddleware = store($component)->find('middlewareAttributes', $method)) return;

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