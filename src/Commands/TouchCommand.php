<?php

namespace Livewire\Commands;

class TouchCommand extends MakeCommand
{
    protected $signature = 'livewire:touch {name} {--force} {--stub=default}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
