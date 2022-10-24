<?php

namespace Livewire\Features\SupportRedirects;

use function Synthetic\after;
use function Synthetic\before;
use function Synthetic\on;
use Livewire\LivewireSynth;
use Livewire\Mechanisms\ComponentDataStore;

class SupportRedirects
{
    public static $redirectorCacheStack = [];

    function boot()
    {
        after('__invoke', function ($component) {
            return function () use ($component) {
                $to = ComponentDataStore::get($component, 'redirect');

                if ($to) {
                    return response()->redirect($to);
                }
            };
        });

        on('component.boot', function ($component) {
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

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            // Put the old redirector back into the container.
            app()->instance('redirect', array_pop(static::$redirectorCacheStack));

            $to = ComponentDataStore::get($target, 'redirect');

            if (! $to) {
                // If there was no redirect. Clear flash session data.
                if (app()->has('session.store')) {
                    session()->forget(session()->get('_flash.new'));
                }

                return;
            };

            $context->addEffect('redirect', $to);
        });
    }
}
