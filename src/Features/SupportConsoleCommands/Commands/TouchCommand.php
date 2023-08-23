<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

class TouchCommand extends MakeCommand
{
    protected $signature = 'livewire:touch {name} {--force} {--inline} {--test} {--pest} {--stub=default}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
