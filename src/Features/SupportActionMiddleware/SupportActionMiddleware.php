<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;
use Livewire\ComponentHook;
use Livewire\Features\SupportEvents\BaseOn;
use Livewire\Features\SupportEvents\SupportEvents;
use Livewire\Features\SupportPageComponents\SupportPageComponents;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

use function Livewire\invade;
use function Livewire\store;

class SupportActionMiddleware extends ComponentHook
{
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
        $listeners = static::getComponentListeners($component);

        $calls = $request->input('components.0.calls');
        
        $call = Arr::first($calls, function ($call) use ($component, $actions, $listeners) {
            $method = static::resolveMethodFromCall($call, $listeners);

            return method_exists($component, $method) && in_array($method, $actions, true);
        });

        $methodName = $call ? static::resolveMethodFromCall($call, $listeners) : null;

        if (! $methodName) return [];

        return static::resolveAttributeMiddleware($component, $methodName);
    }

    protected static function resolveMethodFromCall(array $call, array $listeners)
    {
        $method = $call['method'] ?? '';

        if ($method === '__dispatch') {
            [$name] = $call['params'] ?? [];

            return $listeners[$name] ?? '';
        }

        return $method;
    }

    protected static function resolveAttributeMiddleware($component, $method)
    {
        $reflectionMethod = new \ReflectionMethod($component, $method);
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

        return $listeners;
    }

    protected static function routeActionIsAPageComponent($route)
    {
        return SupportPageComponents::routeActionIsAPageComponent($route);
    }
}