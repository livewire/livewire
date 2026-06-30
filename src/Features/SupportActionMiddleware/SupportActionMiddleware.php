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

            if (static::hasMiddlewareAttribute($component, $method)) {
                app()->instance('redirect', array_pop(SupportRedirects::$redirectorCacheStack));
            }
        });
    }

    static function hasMiddlewareAttribute($component, $method)
    {
        return $component->getAttributes()
            ->filter(fn ($attr) => $attr instanceof BaseMiddleware)
            ->filter(fn ($attr) => $attr->getName() === $method)
            ->isNotEmpty();
    }
}