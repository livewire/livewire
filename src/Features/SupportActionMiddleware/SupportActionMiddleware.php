<?php

namespace Livewire\Features\SupportActionMiddleware;

use Livewire\ComponentHook;
use Livewire\Features\SupportEvents\SupportEvents;
use Livewire\Features\SupportRedirects\SupportRedirects;

use function Livewire\on;

class SupportActionMiddleware extends ComponentHook
{
    public static function provide()
    {
        on('call', function ($component, $method, $params, $componentContext, $earlyReturn) {
            if ($method === '__dispatch') {
                [$name, $params] = $params;

                $method = SupportEvents::getListenerMethodName($component, $name);
            }

            if (method_exists($component, $method) && static::hasMiddlewareAttribute($component, $method)) {
                static::restoreOriginalRedirector();
            }
        });
    }

    protected static function hasMiddlewareAttribute($component, $method)
    {
        return $component->getAttributes()
            ->filter(fn ($attr) => $attr instanceof BaseMiddleware)
            ->filter(fn ($attr) => $attr->getName() === $method)
            ->isNotEmpty();
    }

    protected static function restoreOriginalRedirector()
    {
        $redirectorCacheStack = SupportRedirects::$redirectorCacheStack;

        if ($redirectorCacheStack === []) return;

        $lastIndex = array_key_last($redirectorCacheStack);

        $cachedRedirector = $redirectorCacheStack[$lastIndex];

        if (is_object($cachedRedirector)) {
            app()->instance('redirect', $cachedRedirector);
        }
    }
}