<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

class MakeLivewireCommand extends MakeCommand
{
    protected $signature = 'make:livewire {name} {--force} {--inline} {--test} {--stub=}';
}
