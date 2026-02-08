<?php

namespace Livewire\Mechanisms\FlushState;

use Livewire\Mechanisms\Mechanism;
use function Livewire\trigger;

class FlushState extends Mechanism
{
    public function boot()
    {
        if (class_exists(\Laravel\Octane\Events\RequestTerminated::class)) {
            app('events')->listen(\Laravel\Octane\Events\RequestTerminated::class, function () {
                $this->flush();
            });
        }

        app()->terminating(function () {
            $this->flush();
        });
    }

    public function flush()
    {
        trigger('flush-state');
    }
}
