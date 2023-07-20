<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

class CpCommand extends CopyCommand
{
    protected $signature = 'livewire:cp {name} {new-name} {--inline} {--force} {--test}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
