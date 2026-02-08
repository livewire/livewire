<?php

namespace Livewire\Mechanisms;

class ClearCachedFiles extends Mechanism
{
    function boot()
    {
        // Hook into Laravel's view:clear and optimize:clear command to also clear Livewire compiled files
        $eventCommands = [
            'view:clear',
            'optimize:clear',
        ];

        if (app()->runningInConsole()) {
            app('events')->listen(\Illuminate\Console\Events\CommandFinished::class, function ($event) use ($eventCommands) {
                if (in_array($event->command, $eventCommands) && $event->exitCode === 0) {
                    app('livewire.compiler')->clearCompiled($event->output);
                }
            });
        }
    }
}
