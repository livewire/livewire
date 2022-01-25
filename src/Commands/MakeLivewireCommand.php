<?php

namespace Livewire\Commands;

class MakeLivewireCommand extends MakeCommand
{
    protected $signature = 'make:livewire {name} {--f|--force} {--i|--inline} {--t|--test} {--s|--stub=}';
}
