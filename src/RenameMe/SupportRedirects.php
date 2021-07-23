<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;
use Livewire\Redirector;

class SupportRedirects
{
    static function init() { return new static; }

    public static $redirectorCacheStack = [];

    function __construct()
    {
        Livewire::listen('component.hydrate', function ($component, $request) {
            // Put Laravel's redirector aside and replace it with our own custom one.
            static::$redirectorCacheStack[] = app('redirect');

            app()->bind('redirect', function () use ($component) {
                $redirector = app(Redirector::class)->component($component);

                if (app()->has('session.store')) {
                    $redirector->setSession(app('session.store'));
                }

                return $redirector;
            });
        });

        Livewire::listen('component.dehydrate', function ($component, $response) {
            // Put the old redirector back into the container.
            app()->instance('redirect', array_pop(static::$redirectorCacheStack));

            if (empty($component->redirectTo)) {
                return;
            }

            $response->effects['redirect'] = $component->redirectTo;
            $response->effects['html'] = $response->effects['html'] ?? '<div></div>';
        });

        Livewire::listen('component.dehydrate.subsequent', function ($component, $response) {
            // If there was no redirect. Clear flash session data.
            if (empty($component->redirectTo) && app()->has('session.store')) {
                session()->forget(session()->get('_flash.new'));

                return;
            }
        });
    }
}
