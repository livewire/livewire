<?php

namespace Livewire\Features\SupportRedirects;

use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\ComponentHook;
use Livewire\Component;

class SupportRedirects extends ComponentHook
{
    public static $redirectorCacheStack = [];

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

        if (! $to) {
            // If there was no redirect. Clear flash session data.
            if (app()->has('session.store')) {
                session()->forget(session()->get('_flash.new'));
            }

            return;
        }

        $context->addEffect('redirect', $to);
        $usingNavigate && $context->addEffect('redirectUsingNavigate', true);
    }
}
