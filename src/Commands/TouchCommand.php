<?php

namespace Livewire\Commands;

class TouchCommand extends MakeCommand
{
    protected $signature = 'livewire:touch {name} {--f|--force} {--i|--inline} {--t|--test} {--s|--stub=default}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
