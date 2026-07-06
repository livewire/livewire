<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Routing\Redirector as LaravelRedirector;
use Livewire\ComponentHook;
use Livewire\Exceptions\EventHandlerDoesNotExist;
use Livewire\Features\SupportEvents\SupportEvents;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\Features\SupportRedirects\SupportRedirects;

use function Livewire\after;
use function Livewire\on;

class SupportActionMiddleware extends ComponentHook
{
    public static function provide()
    {
        on('call', function ($component, $method, $params, $componentContext, $earlyReturn, $metadata) {
            $callback = null;

            if ($method === '__dispatch') {
                [$name, $params] = $params;

                $names = SupportEvents::getListenerEventNames($component);

                if (! in_array($name, $names)) {
                    throw new EventHandlerDoesNotExist($name);
                }

                $method = SupportEvents::getListenerMethodName($component, $name);

                $callback = function ($component, $method, $params) {
                    $component->getAttributes()
                        ->filter(fn ($attr) => $attr instanceof BaseMiddleware)
                        ->filter(fn ($attr) => $attr->getName() === $method)
                        ->each(fn ($attr) => $attr->call($params));
                };
            }

            if (method_exists($component, $method) && static::hasMiddlewareAttribute($component, $method)) {
                static::restoreLaravelRedirector();

                if (is_callable($callback)) $callback($component, $method, $params);
            }
        });
        
        after('call', function ($component, $method, $params, $componentContext, $earlyReturn, $metadata) {
            static::bindLivewireRedirector($component);
        });
    }

    protected static function hasMiddlewareAttribute($component, $method)
    {
        return $component->getAttributes()
            ->filter(fn ($attr) => $attr instanceof BaseMiddleware)
            ->filter(fn ($attr) => $attr->getName() === $method)
            ->isNotEmpty();
    }

    protected static function restoreLaravelRedirector()
    {
        $redirectorCacheStack = SupportRedirects::$redirectorCacheStack;

        if ($redirectorCacheStack === []) return;

        $redirector = $redirectorCacheStack[array_key_last($redirectorCacheStack)];

        if (is_object($redirector) && $redirector instanceof LaravelRedirector) {
            app()->instance('redirect', $redirector);
        }
    }

    protected static function bindLivewireRedirector($component)
    {
        app()->bind('redirect', function () use ($component) {
            $redirector = app(Redirector::class)->component($component);

            if (app()->has('session.store')) {
                $redirector->setSession(app('session.store'));
            }

            return $redirector;
        });
    }
}