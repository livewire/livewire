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
        // we need to store all event listener to $listeners property
        // and use all reflected methods to resolve attribute middleware
        $methods = static::getComponentMetadata($component);

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

        return static::resolveAttributeMiddleware($methodName, $methods);
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

    protected static function resolveAttributeMiddleware($method, $methods)
    {
        $reflectionMethod = $methods[$method] ?? null;

        if (! $reflectionMethod) return [];

        $attributes = $reflectionMethod->getAttributes(
            BaseMiddleware::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );

        $middleware = array_map(
            fn ($attribute) => $attribute->newInstance()->middleware,
            $attributes
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

        static::$listeners = $listeners;

        return $methods;
    }

    protected static function routeActionIsAPageComponent($route)
    {
        return SupportPageComponents::routeActionIsAPageComponent($route);
    }
}