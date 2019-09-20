<?php

namespace Livewire\Commands;

class LivewireMvCommand extends LivewireMoveCommand
{
    protected $signature = 'livewire:mv {name} {new-name} {--force}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
