<?php

namespace Livewire;

use WeakMap;
use Livewire\Drawer\Utils;
use Livewire\ComponentHook;

class ComponentHookRegistry
{
    protected static $components;

    protected static $componentHooks = [];

    static function register($hook)
    {
        if (method_exists($hook, 'provide')) $hook::provide();

        if (in_array($hook, static::$componentHooks)) return;

        static::$componentHooks[] = $hook;
    }

    static function boot()
    {
        static::$components = new WeakMap;

        foreach (static::$componentHooks as $hook) {
            on('mount', function ($component, $params, $parent) use ($hook) {
                $hook = static::initializeHook($hook, $component);
                $hook->callBoot();
                $hook->callMount($params, $parent, $parent);
            });

            on('hydrate', function ($target, $memo) use ($hook) {
                $hook = static::initializeHook($hook, $target);
                $hook->callBoot();
                $hook->callHydrate($memo);
            });
        }

        on('update', function ($root, $fullPath, $newValue) {
            if (! is_object($root)) return;

            $propertyName = Utils::beforeFirstDot($fullPath);

            return static::proxyCallToHooks($root, 'callUpdate')($propertyName, $fullPath, $newValue);
        });

        on('call', function ($target, $method, $params, $addEffect, $earlyReturn) {
            if (! is_object($target)) return;

            return static::proxyCallToHooks($target, 'callCall')($method, $params, $earlyReturn);
        });

        on('render', function ($target, $view, $data) {
            return static::proxyCallToHooks($target, 'callRender')($view, $data);
        });

        on('dehydrate', function ($target, $context) {
            static::proxyCallToHooks($target, 'callDehydrate')($context);

            static::proxyCallToHooks($target, 'callDestroy')($context);
        });

        on('exception', function ($target, $e, $stopPropagation) {
            return static::proxyCallToHooks($target, 'callException')($e, $stopPropagation);
        });
    }

    static public function initializeHook($hook, $target)
    {
        if (! isset(static::$components[$target])) static::$components[$target] = [];

        static::$components[$target][] = $hook = new $hook;

        $hook->setComponent($target);

        $propertiesAndMethods = [
            ...(new \ReflectionClass($target))->getProperties(),
            ...(new \ReflectionClass($target))->getMethods(),
        ];

        foreach ($propertiesAndMethods as $property) {
            $attributes = $property->getAttributes();

            foreach ($attributes as $attribute) {
                if (is_subclass_of($attribute->getName(), PropertyHook::class)) {
                    $propertyHook = $attribute->newInstance();

                    $hook->setPropertyHook($property->getName(), $propertyHook);
                }
            }
        }

        return $hook;
    }

    static function proxyCallToHooks($target, $method) {
        return function (...$params) use ($target, $method) {
            $callbacks = [];

            foreach (static::$components[$target] ?? [] as $hook) {
                $callbacks[] = $hook->{$method}(...$params);
            }

            return function (...$forwards) use ($callbacks) {
                foreach ($callbacks as $callback) {
                    $callback(...$forwards);
                }
            };
        };
    }
}
