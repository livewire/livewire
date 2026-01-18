<?php

namespace Livewire\Mechanisms\FlushState;

use Livewire\Mechanisms\Mechanism;
use function Livewire\trigger;

/**
 * Ensures Livewire's static state is flushed at the end of each request.
 *
 * This is critical for long-running processes like Laravel Octane where the
 * application stays in memory between requests. Without flushing, static
 * properties accumulate and cause memory leaks.
 */
class FlushState extends Mechanism
{
    public function boot()
    {
        // For Octane: Listen to RequestTerminated event
        if (class_exists(\Laravel\Octane\Events\RequestTerminated::class)) {
            app('events')->listen(\Laravel\Octane\Events\RequestTerminated::class, function () {
                $this->flush();
            });
        }

        // For all environments: Register a terminating callback
        // This ensures state is flushed after each response is sent
        app()->terminating(function () {
            $this->flush();
        });
    }

    /**
     * Flush all Livewire static state.
     */
    public function flush()
    {
        trigger('flush-state');
    }
}
