<?php

namespace Livewire\Commands;

class MakeLivewireCommand extends MakeCommand
{
    protected $signature = 'make:livewire {name} {--force} {--inline} {--stub=default}';
}
