<?php

namespace Livewire\Mechanisms;

class PrecompileComponents extends Mechanism
{
    function boot()
    {
        // Hook into Laravel's view:cache and optimize commands to also compile every
        // single-file and multi-file component, so no request has to compile one...
        $eventCommands = [
            'view:cache',
            'optimize',
        ];

        if (app()->runningInConsole()) {
            app('events')->listen(\Illuminate\Console\Events\CommandFinished::class, function ($event) use ($eventCommands) {
                if (in_array($event->command, $eventCommands) && $event->exitCode === 0) {
                    app('livewire.compiler')->compileAll(app('livewire.finder')->viewBasedComponentPaths());
                }
            });
        }
    }
}
