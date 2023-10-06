<?php

namespace Livewire;

use WeakMap;
use Livewire\Drawer\Utils;

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

    static function getHook($component, $hook)
    {
        if (! isset(static::$components[$component])) return;

        $componentHooks = static::$components[$component];

        foreach ($componentHooks as $componentHook) {
            if ($componentHook instanceof $hook) return $componentHook;
        }
    }

    static function boot()
    {
        static::$components = new WeakMap;

        foreach (static::$componentHooks as $hook) {
            on('mount', function ($component, $params, $key, $parent) use ($hook) {
                if (! $hook = static::initializeHook($hook, $component)) {
                    return;
                }

                $hook->callBoot();
                $hook->callMount($params, $parent);
            });

            on('hydrate', function ($component, $memo) use ($hook) {
                if (! $hook = static::initializeHook($hook, $component)) {
                    return;
                }

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
        });

        on('destroy', function ($component, $context) {
            static::proxyCallToHooks($component, 'callDestroy')($context);
        });

        on('exception', function ($component, $e, $stopPropagation) {
            static::proxyCallToHooks($component, 'callException')($e, $stopPropagation);
        });
    }

    static public function initializeHook($hook, $target)
    {
        if (! isset(static::$components[$target])) static::$components[$target] = [];

        $hook = new $hook;

        $hook->setComponent($target);

        // If no `skip` method has been implemented, then boot the hook anyway
        if (method_exists($hook, 'skip') && $hook->skip()) {
            return;
        }

        static::$components[$target][] = $hook;

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
