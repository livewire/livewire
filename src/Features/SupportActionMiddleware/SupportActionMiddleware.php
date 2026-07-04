<?php

namespace Livewire\Features\SupportActionMiddleware;

use Livewire\ComponentHook;
use Livewire\Features\SupportEvents\BaseOn;
use Livewire\Features\SupportPageComponents\SupportPageComponents;

class SupportActionMiddleware extends ComponentHook
{
    public static function gatherActionMiddleware($request, $route)
    {
        if (! $component = static::routeActionIsAPageComponent($route)) {
            return [];
        }

        // Since an action can be called as event listener
        // we need to retrieve all that using middleware attribute
        [$actions, $listeners] = static::getComponentMetadata($component);

        $calls = $request->array('components.0.calls', []);

        $methodName = null;
        foreach ($calls as $call) {
            $method = static::resolveMethodFromCall($call, $listeners);

            if (method_exists($component, $method) && in_array($method, array_keys($actions), true)) {
                $methodName = $method;
                break;
            }
        }

        if (! $methodName) return [];

        $middleware = static::resolveAttributeMiddleware($methodName, $actions);

        return tap($middleware, $route->middleware(...));
    }

    protected static function resolveMethodFromCall($call, $listeners)
    {
        $method = $call['method'] ?? '';

        if ($method === '__dispatch') {
            [$name] = $call['params'] ?? [];

            return $listeners[$name] ?? '';
        }

        return $method;
    }

    protected static function resolveAttributeMiddleware($method, $actions)
    {
        $reflectionMethod = $actions[$method] ?? null;

        if (! $reflectionMethod) return [];

        $middleware = array_map(
            fn ($attribute) => $attribute->newInstance()->middleware,
            $reflectionMethod->getAttributes(BaseMiddleware::class, \ReflectionAttribute::IS_INSTANCEOF)
        );

        return app('router')->resolveMiddleware($middleware);
    }

    protected static function getComponentMetadata($component)
    {
        $reflectionMethods = (new \ReflectionClass($component))
            ->getMethods(\ReflectionMethod::IS_PUBLIC);

        $methods = [];
        $listeners = [];
        foreach ($reflectionMethods as $method) {
            if (! $method->getAttributes(BaseMiddleware::class, \ReflectionAttribute::IS_INSTANCEOF)) {
                continue;
            }

            $methods[$method->getName()] = $method;

            foreach ($method->getAttributes(BaseOn::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $listeners[$attribute->getArguments()[0]] = $method->getName();
            }
        }

        return [$methods, $listeners];
    }

    protected static function routeActionIsAPageComponent($route)
    {
        return SupportPageComponents::routeActionIsAPageComponent($route);
    }
}