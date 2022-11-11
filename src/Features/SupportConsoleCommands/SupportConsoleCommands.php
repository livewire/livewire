<?php

namespace Livewire\Features\SupportConsoleCommands;

use Illuminate\Console\Application as Artisan;

class SupportConsoleCommands
{
    function boot()
    {
        if (! app()->runningInConsole()) return;

        $this->commands([
            \Livewire\Features\SupportConsoleCommands\Commands\MakeLivewireCommand::class, // make:livewire
            \Livewire\Features\SupportConsoleCommands\Commands\MakeCommand::class,         // livewire:make
            \Livewire\Features\SupportConsoleCommands\Commands\TouchCommand::class,        // livewire:touch
            \Livewire\Features\SupportConsoleCommands\Commands\CopyCommand::class,         // livewire:copy
            \Livewire\Features\SupportConsoleCommands\Commands\CpCommand::class,           // livewire:cp
            \Livewire\Features\SupportConsoleCommands\Commands\DeleteCommand::class,       // livewire:delete
            \Livewire\Features\SupportConsoleCommands\Commands\RmCommand::class,           // livewire:rm
            \Livewire\Features\SupportConsoleCommands\Commands\MoveCommand::class,         // livewire:move
            \Livewire\Features\SupportConsoleCommands\Commands\MvCommand::class,           // livewire:mv
            \Livewire\Features\SupportConsoleCommands\Commands\StubsCommand::class,        // livewire:stubs
            \Livewire\Features\SupportConsoleCommands\Commands\S3CleanupCommand::class,    // livewire:configure-s3-upload-cleanup
            \Livewire\Features\SupportConsoleCommands\Commands\PublishCommand::class,      // livewire:publish
        ]);
    }

    public function commands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        Artisan::starting(function ($artisan) use ($commands) {
            $artisan->resolveCommands($commands);
        });
    }
}
