<?php

namespace Livewire\Features\SupportConsoleCommands;

use Illuminate\Console\Application as Artisan;
use Livewire\ComponentHook;

class SupportConsoleCommands extends ComponentHook
{
    static function provide()
    {
        if (! app()->runningInConsole()) return;

        static::commands([
            \Livewire\Features\SupportConsoleCommands\Commands\MakeLivewireCommand::class, // make:livewire
            \Livewire\Features\SupportConsoleCommands\Commands\MakeCommand::class,         // livewire:make
            \Livewire\Features\SupportConsoleCommands\Commands\FormCommand::class,         // livewire:form
            \Livewire\Features\SupportConsoleCommands\Commands\AttributeCommand::class,    // livewire:attribute
            \Livewire\Features\SupportConsoleCommands\Commands\TouchCommand::class,        // livewire:touch
            \Livewire\Features\SupportConsoleCommands\Commands\CopyCommand::class,         // livewire:copy
            \Livewire\Features\SupportConsoleCommands\Commands\CpCommand::class,           // livewire:cp
            \Livewire\Features\SupportConsoleCommands\Commands\DeleteCommand::class,       // livewire:delete
            \Livewire\Features\SupportConsoleCommands\Commands\LayoutCommand::class,       // livewire:layout
            \Livewire\Features\SupportConsoleCommands\Commands\RmCommand::class,           // livewire:rm
            \Livewire\Features\SupportConsoleCommands\Commands\MoveCommand::class,         // livewire:move
            \Livewire\Features\SupportConsoleCommands\Commands\MvCommand::class,           // livewire:mv
            \Livewire\Features\SupportConsoleCommands\Commands\StubsCommand::class,        // livewire:stubs
            \Livewire\Features\SupportConsoleCommands\Commands\S3CleanupCommand::class,    // livewire:configure-s3-upload-cleanup
            \Livewire\Features\SupportConsoleCommands\Commands\PublishCommand::class,      // livewire:publish
            \Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand::class,      // livewire:upgrade
        ]);
    }

    static function commands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        Artisan::starting(fn(Artisan $artisan) => $artisan->resolveCommands($commands));
    }
}
