<?php

namespace Livewire\Commands;

class LivewireMVCommand extends LivewireMoveCommand
{
    protected $signature = 'livewire:mv {name} {--force}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
