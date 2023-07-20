<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

class MvCommand extends MoveCommand
{
    protected $signature = 'livewire:mv {name} {new-name} {--inline} {--force}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
