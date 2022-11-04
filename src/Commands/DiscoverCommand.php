<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Livewire\LivewireComponentsFinder;
use Livewire\Mechanisms\ComponentRegistry;

class DiscoverCommand extends Command
{
    protected $signature = 'livewire:discover';

    protected $description = 'Regenerate Livewire component auto-discovery manifest';

    public function handle()
    {
        app(ComponentRegistry::class)->buildManifest();

        $this->info('Livewire auto-discovery manifest rebuilt!');
    }
}
