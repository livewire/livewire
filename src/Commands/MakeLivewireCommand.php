<?php

namespace Livewire\Commands;

class MakeLivewireCommand extends LivewireMakeCommand
{
    protected $signature = 'make:livewire {name} {--force}';

    protected $description = 'Create a new Livewire component and it\'s corresponding blade view.';
}
