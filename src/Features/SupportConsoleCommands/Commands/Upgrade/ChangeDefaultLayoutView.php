<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ChangeDefaultLayoutView extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        if($this->hasOldLayout())
        {
            $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> The Livewire default layout has has changed. </>");
            $console->newLine();

            $console->line('When rendering full-page components Livewire would use the "layouts.app" view as the default layout. This has been changed to "components.layouts.app".');

            $choice = $console->choice('Would you like to migrate or keep the old layout?', [
                'migrate',
                'keep',
            ], 'migrate');

            if($choice == 'keep') {
                $console->line('Keeping the old default layout...');

                $this->publishConfigIfMissing($console);

                $console->line('Setting the default layout to "layouts.app"...');

                $this->patternReplacement('/components\.layouts\.app/', 'layouts.app', 'config');

                return $next($console);
            }

            $console->line('Setting the default layout to "components.layouts.app"...');

            $this->patternReplacement('/layouts\.app/', 'components.layouts.app', 'config');
        }

        return $next($console);
    }

    protected function hasOldLayout()
    {
        return config('livewire.class_namespace') === 'layouts.app' || $this->filesystem()->exists('resources/views/layouts/app.blade.php');
    }
}
