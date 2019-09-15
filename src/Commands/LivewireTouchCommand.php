<?php

namespace Livewire\Commands;

class LivewireTouchCommand extends LivewireMakeCommand
{
    protected $signature = 'livewire:touch {name} {--force}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
