<?php

namespace Livewire\V4\Compiler\Commands;

use Illuminate\Console\Command;
use Livewire\V4\Compiler\BladeStyleCompiler;

class LivewireClearCommand extends Command
{
    protected $signature = 'livewire:clear';
    protected $description = 'Clear Livewire V4 component cache and Laravel\'s view cache';

    public function handle()
    {
        $compiler = new BladeStyleCompiler();
        $compiler->clearCache();
        $this->info('Livewire component cache cleared.');

        $this->info('Clearing Laravel\'s view cache...');
        $this->call('view:clear');
        $this->info('Laravel\'s view cache cleared.');

        $this->info('Livewire cache and view cache cleared successfully.');
    }
}