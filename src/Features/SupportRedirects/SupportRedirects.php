<?php

namespace Livewire\Features\SupportRedirects;

use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\ComponentHook;
use Livewire\Component;
use function Livewire\on;

class SupportRedirects extends ComponentHook
{
    public static $redirectorCacheStack = [];
    public static $atLeastOneMountedComponentHasRedirected = false;

    public static function provide()
    {
        // Wait until all components have been processed...
        on('response', function ($response) {
            // If there was no redirect on a subsequent component update, clear flash session data.
            if (! static::$atLeastOneMountedComponentHasRedirected && app()->has('session.store')) {
                session()->forget(session()->get('_flash.new'));
            }
        });

        on('flush-state', function () {
            static::$atLeastOneMountedComponentHasRedirected = false;
        });
    }

    public function boot()
    {
        // Put Laravel's redirector aside and replace it with our own custom one.
        static::$redirectorCacheStack[] = app('redirect');

        app()->bind('redirect', function () {
            $redirector = app(Redirector::class)->component($this->component);

            if (app()->has('session.store')) {
                $redirector->setSession(app('session.store'));
            }

            return $redirector;
        });
    }

    public function dehydrate($context)
    {
        // Put the old redirector back into the container.
        app()->instance('redirect', array_pop(static::$redirectorCacheStack));

        $to = $this->storeGet('redirect');
        $usingNavigate = $this->storeGet('redirectUsingNavigate');

        if (is_subclass_of($to, Component::class)) {
            $to = url()->action($to);
        }

        if ($to && ! app(HandleRequests::class)->isLivewireRequest()) {
            abort(redirect($to));
        }

        if (! $to) return;

        $context->addEffect('redirect', $to);
        $usingNavigate && $context->addEffect('redirectUsingNavigate', true);

        if (! $context->isMounting()) {
            static::$atLeastOneMountedComponentHasRedirected = true;
        }
    }
}
