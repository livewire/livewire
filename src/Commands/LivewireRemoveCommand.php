<?php

namespace Livewire\Commands;

class LivewireRemoveCommand extends LivewireDestroyCommand
{
    protected $signature = 'livewire:rm {name} {--force}';
}
