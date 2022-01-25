<?php

namespace Livewire\Commands;

class RmCommand extends DeleteCommand
{
    protected $signature = 'livewire:rm {name} {--i|--inline} {--f|--force} {--t|--test}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
