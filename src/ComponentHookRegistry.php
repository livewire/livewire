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

        on('update', function ($component, $fullPath, $newValue) {
            $propertyName = Utils::beforeFirstDot($fullPath);

            return static::proxyCallToHooks($component, 'callUpdate')($propertyName, $fullPath, $newValue);
        });

        on('call', function ($component, $method, $params, $addEffect, $earlyReturn) {
            return static::proxyCallToHooks($component, 'callCall')($method, $params, $earlyReturn);
        });

        on('render', function ($component, $view, $data) {
            return static::proxyCallToHooks($component, 'callRender')($view, $data);
        });

        on('dehydrate', function ($component, $context) {
            static::proxyCallToHooks($component, 'callDehydrate')($context);

            static::proxyCallToHooks($component, 'callDestroy')($context);
        });

        on('exception', function ($component, $e, $stopPropagation) {
            return static::proxyCallToHooks($component, 'callException')($e, $stopPropagation);
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
