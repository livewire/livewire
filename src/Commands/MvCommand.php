<?php

namespace Livewire\Commands;

class MvCommand extends MoveCommand
{
    protected $signature = 'livewire:mv {name} {new-name} {--i|--inline} {--f|--force} {--t|--test}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
