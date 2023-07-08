<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

abstract class UpgradeStep
{
    public function filesystem(): FilesystemAdapter
    {
        return Storage::build([
            'driver' => 'local',
            'root' => base_path(),
        ]);
    }

    public function interactiveReplacement($console, $title, $before, $after, $pattern, $replacement, $directories = ['resources/views'])
    {
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> {$title} </>");
        $console->newLine();

        $console->line("This means all <options=underscore>{$before}</> directives must be changed to <options=underscore>{$after}</>.");

        $confirm = $console->confirm("Would you like to change all occurrences of {$before} to {$after}?", true);

        if ($confirm) {
            $console->line("Changing all occurrences of <options=underscore>{$before}</> to <options=underscore>{$after}</>.");
            $console->newLine();

            $console->table(['File', 'Occurrences'], $this->patternReplacement($pattern, $replacement, $directories));
        }

        $console->newLine(4);
    }

    public function patternReplacement($pattern, $replacement, $directories = ['resources/views'])
    {
        return collect($directories)->map(function($directory) use ($pattern, $replacement) {
            return collect($this->filesystem()->allFiles($directory))
                ->map(function ($viewPath) {
                    return [
                        'path' => $viewPath,
                        'content' => $this->filesystem()->get($viewPath),
                    ];
                })->map(function($view) use ($pattern, $replacement) {
                    $view['content'] = preg_replace($pattern, $replacement, $view['content'], -1, $count);
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
                });
        })->flatten(1);
    }
}
