<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Livewire\Features\SupportConsoleCommands\Commands\UpgradeCommand;

class AddLiveModifierToWireModelDirectives extends UpgradeStep
{
    public function handle(UpgradeCommand $console, \Closure $next)
    {
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> The wire:model directive is now deferred by default. </>");
        $console->newLine();
        $console->line('This means all <options=underscore>wire:model</> directives must be changed to <options=underscore>wire:model.live</>.');

        $confirm = $console->confirm('Would you like to change all occurences of wire:model to wire:model.live?', true);

        if (! $confirm) {
            return $next($console);
        }

        $console->line('Changing all occurrences of wire:model to wire:model.live...');
        $console->newLine();
        
        $console->table(
            ['File', 'Occurrences'],
            collect($this->filesystem()->allFiles('resources/views'))
                ->map(function ($viewPath) {
                    return [
                        'path' => $viewPath,
                        'content' => $this->filesystem()->get($viewPath),
                    ];
                })->map(function($view) {
                    $view['content'] = preg_replace('/wire:model(?!\.(?:defer|lazy|live))/', 'wire:model.live', $view['content'], -1, $count);
                    $view['occurrences'] = $count;

                    return $count > 0 ? $view : null;
                })
                ->filter()
                ->values()
                ->map(function($view) {
                    $this->filesystem()->put($view['path'], $view['content']);

                    return [
                        $view['path'], $view['occurrences'],
                    ];
                })
        );

        return $next($console);
    }
}
