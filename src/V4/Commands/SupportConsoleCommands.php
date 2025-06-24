<?php

namespace Livewire\V4\Commands;

use Illuminate\Console\Application as Artisan;
use Livewire\ComponentHook;

class SupportConsoleCommands extends ComponentHook
{
    public static function provide()
    {
        if (! app()->runningInConsole()) {
            return;
        }

        static::commands([
            ConvertSfcCommand::class,
        ]);
    }

    public static function commands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        Artisan::starting(fn (Artisan $artisan) => $artisan->resolveCommands($commands));
    }
}
