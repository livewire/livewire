<?php

namespace Livewire\Commands;

class MvCommand extends MoveCommand
{
    protected $signature = 'livewire:mv {name} {new-name} {--force}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
