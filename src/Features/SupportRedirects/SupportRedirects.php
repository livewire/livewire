<?php

namespace Livewire\Features\SupportRedirects;

use function Livewire\short;
use function Livewire\store;
use function Livewire\after;
use function Livewire\before;
use function Livewire\on;

use Livewire\ComponentHook;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\Mechanisms\DataStore;
use Synthetic\ShortcircuitResponse;

class SupportRedirects extends ComponentHook
{
    public $redirectorCacheStack = [];

    public function boot()
    {
        // Put Laravel's redirector aside and replace it with our own custom one.
        $this->redirectorCacheStack[] = app('redirect');

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
        app()->instance('redirect', array_pop($this->redirectorCacheStack));

        $to = $this->storeGet('redirect');

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
    }
}
