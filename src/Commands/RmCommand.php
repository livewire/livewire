<?php

namespace Livewire\Commands;

class RmCommand extends DeleteCommand
{
    protected $signature = 'livewire:rm {name} {--force}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
