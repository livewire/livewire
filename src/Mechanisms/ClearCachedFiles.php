<?php

namespace Livewire\Mechanisms;

class ClearCachedFiles extends Mechanism
{
    function boot()
    {
        // Hook into Laravel's view:clear command to also clear Livewire compiled files
        if (app()->runningInConsole()) {
            app('events')->listen(\Illuminate\Console\Events\CommandFinished::class, function ($event) {
                if ($event->command === 'view:clear' && $event->exitCode === 0) {
                    app('livewire.compiler')->clearCompiled($event->output);
                }
            });
        }
    }
}
