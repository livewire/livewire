<?php

namespace Livewire\Commands;

class LivewireRmCommand extends LivewireDeleteCommand
{
    protected $signature = 'livewire:rm {name} {--force}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
