<?php

namespace Livewire\Features\SupportRedirects;

use function Livewire\short;
use function Livewire\store;
use function Synthetic\after;
use function Synthetic\before;
use function Synthetic\on;
use Livewire\LivewireSynth;
use Livewire\Mechanisms\DataStore;
use Synthetic\ShortcircuitResponse;

class SupportRedirects
{
    public static $redirectorCacheStack = [];

    function boot()
    {
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

            $to = store($target)->get('redirect');

            if ($to && ! app('livewire')->isLivewireRequest()) {
                abort(redirect($to));
            }

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
