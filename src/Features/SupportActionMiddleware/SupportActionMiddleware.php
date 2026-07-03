<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Livewire\ComponentHook;
use Livewire\Features\SupportEvents\BaseOn;
use Livewire\Features\SupportPageComponents\SupportPageComponents;

use function Livewire\on;
use function Livewire\store;

class SupportActionMiddleware extends ComponentHook
{
    protected static $listeners = [];

    public static function provide()
    {
        on('flush-state', function () {
            static::$listeners = [];
        });
    }

    // Since this might runs before all component hooks (e.g on snapshot verified)
    // we need to retrieve all methods from middleware attribute on dehydrate
    // and store it in component payload memo so later can be used to determine method name
    public static function getActionNameFromComponent($component)
    {
        // Contains [middleware => method]
        $middlewareFromAttributes = store($component)->get('middlewareFromAttributes', []);

        return collect($middlewareFromAttributes)
            ->values()
            ->unique()
            ->all();
    }

    public static function gatherActionMiddleware(Request $request, array $actions)
    {
        if (! $componentClass = static::routeActionIsAPageComponent($request->route())) {
            return [];
        }

        $component = new $componentClass;

        // Since an action can be called as event listener
        // we need to retrieve all event listener to resolve method name
        $reflectionMethods = static::getComponentListeners($component);

        $calls = $request->input('components.0.calls');
        
        $methodName = null;
        foreach ($calls as $call) {
            $method = static::resolveMethodFromCall($call);

            if (method_exists($component, $method) && in_array($method, $actions, true)) {
                $methodName = $method;
                break;
            }
        }

        if (! $methodName) return [];

        return static::resolveAttributeMiddleware($methodName, $reflectionMethods);
    }

    protected static function resolveMethodFromCall(array $call)
    {
        $method = $call['method'] ?? '';

        if ($method === '__dispatch') {
            [$name] = $call['params'] ?? [];

            return static::$listeners[$name] ?? '';
        }

        return $method;
    }

    protected static function resolveAttributeMiddleware($method, $reflectionMethods)
    {
        $reflectionMethod = Arr::first($reflectionMethods, function ($reflected) use ($method) {
            return $reflected->getName() === $method;
        });

        $attributes = $reflectionMethod->getAttributes(BaseMiddleware::class, \ReflectionAttribute::IS_INSTANCEOF);

        $arguments = collect($attributes)
            ->map(fn ($attr) => $attr->newInstance()->middleware)
            ->values()
            ->all();

        return app('router')->resolveMiddleware($arguments);
    }

    protected static function getComponentListeners($component)
    {
        $reflectionClass = new \ReflectionClass($component);
        $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $hasMiddlewareAttribute = array_filter($reflectionMethods, function ($method) {
            return $method->getAttributes(BaseOn::class, \ReflectionAttribute::IS_INSTANCEOF)
                && $method->getAttributes(BaseMiddleware::class, \ReflectionAttribute::IS_INSTANCEOF);
        });

        $listeners = [];
        foreach ($hasMiddlewareAttribute as $method) {
            foreach ($method->getAttributes() as $attribute) {
                if (is_subclass_of($attribute->getName(), BaseOn::class)) {
                    $arguments = $attribute->getArguments();
                    $listeners[$arguments[0]] = $method->getName();
                }
            }
        }

        static::$listeners = $listeners;

        return $reflectionMethods;
    }

    protected static function routeActionIsAPageComponent($route)
    {
        return SupportPageComponents::routeActionIsAPageComponent($route);
    }
}