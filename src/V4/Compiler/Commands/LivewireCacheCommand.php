<?php

namespace Livewire\V4\Compiler\Commands;

use Illuminate\Console\Command;
use Livewire\V4\Compiler\BladeStyleCompiler;

class LivewireCacheCommand extends Command
{
    protected $signature = 'livewire:cache {action=stats : Action to perform (clear, stats)}';
    protected $description = 'Manage Livewire V4 component cache (works like Blade cache)';

    public function handle()
    {
        $action = $this->argument('action');
        $compiler = new BladeStyleCompiler();

        switch ($action) {
            case 'clear':
                $compiler->clearCache();
                $this->info('Livewire component cache cleared.');
                break;

            case 'stats':
                $stats = $compiler->getStats();
                $this->info('Livewire Component Cache Statistics:');
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Total Compiled Components', $stats['total_compiled']],
                        ['In-Memory Cache', $stats['memory_cached']],
                        ['Cache Directory', $stats['cache_directory']],
                    ]
                );
                break;

            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }

        return 0;
    }
}