<?php

namespace Livewire;

use WeakMap;
use Livewire\Drawer\Utils;

class ComponentHookRegistry
{
    protected static $components;

    protected static $componentHooks = [];

    protected static $activeHooks = [];

    protected static $emptyFinisher;

    static function register($hook)
    {
        if (method_exists($hook, 'provide')) $hook::provide();

        if (in_array($hook, static::$componentHooks)) return;

        static::$componentHooks[] = $hook;

        $hasInstanceLifecycle = false;
        $methods = ['boot', 'mount', 'hydrate', 'update', 'call', 'render', 'renderIsland', 'renderPlaceholder', 'dehydrate', 'destroy', 'exception'];
        foreach ($methods as $m) {
            if (method_exists($hook, $m)) {
                $hasInstanceLifecycle = true;
                break;
            }
        }

        if ($hasInstanceLifecycle || method_exists($hook, 'skip') || static::hasCustomInstanceMembers($hook)) {
            static::$activeHooks[] = $hook;
        }
    }

    protected static function hasCustomInstanceMembers($hook): bool
    {
        $ref = new \ReflectionClass($hook);

        foreach ($ref->getMethods() as $method) {
            if ($method->isStatic()) continue;
            if ($method->getName() === '__construct') continue;
            if ($method->getDeclaringClass()->getName() === ComponentHook::class) continue;
            return true;
        }

        foreach ($ref->getProperties() as $property) {
            if ($property->isStatic()) continue;
            if ($property->getDeclaringClass()->getName() === ComponentHook::class) continue;
            return true;
        }

        return false;
    }

    static function getHook($component, $hook)
    {
        if (isset(static::$components[$component][$hook])) {
            return static::$components[$component][$hook];
        }

        if (isset(static::$components[$component])) {
            foreach (static::$components[$component] as $componentHook) {
                if ($componentHook instanceof $hook) return $componentHook;
            }
        }

        if (in_array($hook, static::$componentHooks)) {
            return static::initializeHook($hook, $component);
        }

        return null;
    }

    static function boot()
    {
        static::$components = new WeakMap;

        on('flush-state', function () {
            static::$components = new WeakMap;
        });

        on('mount', function ($component, $params, $key, $parent, $attributes) {
            foreach (static::$activeHooks as $hook) {
                if (! $instance = static::initializeHook($hook, $component)) {
                    continue;
                }

                $instance->callBoot();
                $instance->callMount($params, $parent, $attributes);
            }
        });

        on('hydrate', function ($component, $memo) {
            foreach (static::$activeHooks as $hook) {
                if (! $instance = static::initializeHook($hook, $component)) {
                    continue;
                }

                $instance->callBoot();
                $instance->callHydrate($memo);
            }
        });

        on('update', function ($component, $fullPath, $newValue) {
            $propertyName = Utils::beforeFirstDot($fullPath);

            return static::proxyCallToHooks($component, 'callUpdate')($propertyName, $fullPath, $newValue);
        });

        on('call', function ($component, $method, $params, $componentContext, $earlyReturn, $metadata) {
            return static::proxyCallToHooks($component, 'callCall')($method, $params, $earlyReturn, $metadata, $componentContext);
        });

        on('render', function ($component, $view, $data) {
            return static::proxyCallToHooks($component, 'callRender')($view, $data);
        });

        on('renderIsland', function ($component, $name, $view, $data) {
            return static::proxyCallToHooks($component, 'callRenderIsland')($name, $view, $data);
        });

        on('render.placeholder', function ($component, $view, $data) {
            return static::proxyCallToHooks($component, 'callRenderPlaceholder')($view, $data);
        });

        on('dehydrate', function ($component, $context) {
            static::proxyCallToHooks($component, 'callDehydrate')($context);
        });

        on('destroy', function ($component, $context) {
            static::proxyCallToHooks($component, 'callDestroy')($context);
        });

        on('exception', function ($target, $e, $stopPropagation) {
            if ($target instanceof \Livewire\Features\SupportFormObjects\Form) {
                $target = $target->getComponent();
            }

            if ($target instanceof \Livewire\Component) {
                static::proxyCallToHooks($target, 'callException')($e, $stopPropagation);
            }
        });
    }

    static public function initializeHook($hook, $target)
    {
        if (! isset(static::$components[$target])) static::$components[$target] = [];

        $instance = new $hook;

        $instance->setComponent($target);

        // If no `skip` method has been implemented, then boot the hook anyway
        if (method_exists($instance, 'skip') && $instance->skip()) {
            return null;
        }

        return static::$components[$target][$hook] = $instance;
    }

    static function proxyCallToHooks($target, $method) {
        return function (...$params) use ($target, $method) {
            if (! isset(static::$components[$target])) {
                return static::$emptyFinisher ??= static fn () => null;
            }

            $forwardCallbacks = [];

            foreach (static::$components[$target] as $hook) {
                if ($callback = $hook->{$method}(...$params)) {
                    $forwardCallbacks[] = $callback;
                }
            }

            if (empty($forwardCallbacks)) {
                return static::$emptyFinisher ??= static fn () => null;
            }

            return function (...$forwards) use ($forwardCallbacks) {
                foreach ($forwardCallbacks as $callback) {
                    $callback(...$forwards);
                }
            };
        };
    }
}
