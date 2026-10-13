<?php

namespace Livewire\Mechanisms;

use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

class CacheCompiledFiles extends Mechanism
{
    function boot()
    {
        // Hook into Laravel's view:cache and optimize commands to also compile every
        // view-based component, so no request has to compile one...
        $eventCommands = [
            'view:cache',
            'optimize',
        ];

        if (app()->runningInConsole()) {
            app('events')->listen(\Illuminate\Console\Events\CommandFinished::class, function ($event) use ($eventCommands) {
                if (in_array($event->command, $eventCommands) && $event->exitCode === 0) {
                    $compiler = app('livewire.compiler');

                    $compiler->compileAll(app('livewire.finder')->getViewBasedComponentPaths());

                    // Livewire's own directives and precompilers apply only while a component renders...
                    $undo = app(ExtendBlade::class)->livewireifyBladeCompiler();

                    try {
                        $compiler->compileGeneratedViews();
                    } finally {
                        $undo();
                    }
                }
            });
        }
    }
}
