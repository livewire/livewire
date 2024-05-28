<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Console\Command;

class UpgradeConfigInstructions extends UpgradeStep
{
    public function handle(Command $console, \Closure $next)
    {
        $console->line('<fg=#FB70A9;bg=black;options=bold,reverse> Manual Upgrade: New configuration </>');
        $console->newLine();
        $console->line('Livewire V3 has both added and removed certain configuration items.');
        $console->line('If your application has a published configuration file `config/livewire.php`, you will need to update it to account for the following changes.');
        $console->line('Added options: legacy_model_binding, inject_assets, inject_morph_markers, and navigate');
        $console->line('Removed options: app_url, middleware_group, manifest_path, back_button_cache');
        $console->newLine();

        if($console->confirm('Continue?', true))
        {
            return $next($console);
        }
    }
}
