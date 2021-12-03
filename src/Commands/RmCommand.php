<?php

namespace Livewire\Commands;

class RmCommand extends DeleteCommand
{
    protected $signature = 'livewire:rm {name} {--inline} {--force} {--test} {--pest}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
