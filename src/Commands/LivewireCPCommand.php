<?php

namespace Livewire\Commands;

class LivewireCpCommand extends LivewireCopyCommand
{
    protected $signature = 'livewire:cp {name} {new-name} {--force}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
