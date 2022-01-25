<?php

namespace Livewire\Commands;

class CpCommand extends CopyCommand
{
    protected $signature = 'livewire:cp {name} {new-name} {--i|--inline} {--f|--force} {--t|--test}';

    protected function configure()
    {
        $this->setHidden(true);
    }
}
