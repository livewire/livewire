<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\ComponentParser;
use Livewire\Features\SupportConsoleCommands\Commands\ComponentParserFromExistingComponent;
use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class ChangeDefaultNamespace extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        if($this->hasOldNamespace())
        {
            $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> The Livewire namespace has changed. </>");
            $console->newLine();

            $console->line('The <options=underscore>App\\Http\\Livewire</> namespace was detected and is no longer the default in Livewire v3. Livewire v3 now uses the <options=underscore>App\\Livewire</> namespace.');

            $choice = $console->choice('Would you like to migrate or keep the old namespace?', [
                'migrate',
                'keep',
            ], 'migrate');

            if($choice == 'keep') {
                $console->line('Keeping the old namespace...');

                $this->publishConfigIfMissing($console);

                $console->line('Setting the default namespace to "App\\Http\\Livewire"...');

                $config = $this->filesystem()->get('config/livewire.php');
                $config = str_replace('App\\\\Livewire', 'App\\\\Http\\\\Livewire', $config);
                $this->filesystem()->put('config/livewire.php', $config);

                return $next($console);
            }

            $results = collect($this->filesystem()->allFiles('app/Http/Livewire'))->map(function($file) {
                return str($file)->after('app/Http/Livewire/')->before('.php')->__toString();
            })->map(function($component) {
                $parser = new ComponentParser(
                    'App\\Http\\Livewire',
                    config('livewire.view_path'),
                    $component,
                );

                $newParser = new ComponentParserFromExistingComponent(
                    'App\\Livewire',
                    config('livewire.view_path'),
                    $component,
                    $parser
                );

                if ($this->filesystem()->exists($newParser->relativeClassPath())) {
                    return ['Skipped', $component, 'Already exists'];

                    return false;
                }

                if($this->filesystem()->directoryMissing(dirname($newParser->relativeClassPath()))) {
                    $this->filesystem()->createDirectory(dirname($newParser->relativeClassPath()));
                }

                $this->filesystem()->put($newParser->relativeClassPath(), $newParser->classContents());
                $this->filesystem()->delete($parser->relativeClassPath());

                return ['Migrated', $component];
            });

            $console->table(
                ['Status', 'Component', 'Remark'], $results
            );

            return $next($console);
        }
    }

    protected function hasOldNamespace()
    {
        return $this->filesystem()->exists('app/Http/Livewire') || config('livewire.class_namespace') === 'App\\Http\\Livewire';
    }
}
