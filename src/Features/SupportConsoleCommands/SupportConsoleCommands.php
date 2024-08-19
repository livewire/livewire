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
            Commands\FluxInstallCommand::class,  // flux:install
            Commands\MakeLivewireCommand::class, // make:livewire
            Commands\MakeCommand::class,         // livewire:make
            Commands\FormCommand::class,         // livewire:form
            Commands\AttributeCommand::class,    // livewire:attribute
            Commands\TouchCommand::class,        // livewire:touch
            Commands\CopyCommand::class,         // livewire:copy
            Commands\CpCommand::class,           // livewire:cp
            Commands\DeleteCommand::class,       // livewire:delete
            Commands\LayoutCommand::class,       // livewire:layout
            Commands\RmCommand::class,           // livewire:rm
            Commands\MoveCommand::class,         // livewire:move
            Commands\MvCommand::class,           // livewire:mv
            Commands\StubsCommand::class,        // livewire:stubs
            Commands\S3CleanupCommand::class,    // livewire:configure-s3-upload-cleanup
            Commands\PublishCommand::class,      // livewire:publish
            Commands\UpgradeCommand::class,      // livewire:upgrade
        ]);
    }

    static function commands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        Artisan::starting(fn(Artisan $artisan) => $artisan->resolveCommands($commands));
    }
}
