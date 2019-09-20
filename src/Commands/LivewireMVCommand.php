<?php

namespace Livewire\Commands;

class LivewireMvCommand extends LivewireMoveCommand
{
    protected $signature = 'livewire:mv {name} {newName} {--force}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
