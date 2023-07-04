<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

abstract class UpgradeStep
{
    public function filesystem(): FilesystemAdapter
    {
        return Storage::build([
            'driver' => 'local',
            'root' => base_path(),
        ]);
    }
}
