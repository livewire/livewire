<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Arr;
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

    public function publishConfigIfMissing($console): bool
    {
        if($this->filesystem()->missing('config/livewire.php')) {
            $console->line('Publishing Livewire config file...');
            $console->newLine();

            $console->call('vendor:publish', [
                '--tag' => 'livewire:config',
            ]);

            return true;
        }

        return false;
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

            $replacements = $this->patternReplacement($pattern, $replacement, $directories);

            if($replacements->isEmpty())
            {
                $console->line("No occurrences of <options=underscore>{$before}</> were found.");
            }

            if($replacements->isNotEmpty()) {
                $console->table(['File', 'Occurrences'], $replacements);
            }
        }

        $console->newLine(4);
    }

    public function patternReplacement(
        $pattern,
        $replacement,
        $directories = 'resources/views',
        $files = [],
        $mode = 'auto')
    {
        // If the mode is auto, we'll just get all the files in the directories
        if($mode == 'auto') {
            $files = collect(Arr::wrap($directories))->map(function($directory) {
                return collect($this->filesystem()->allFiles($directory))->map(function ($path) {
                    return [
                        'path' => $path,
                        'content' => $this->filesystem()->get($path),
                    ];
                });
            })->flatten(1);
        }

        // If the mode is manual, we'll just use the files passed in
        if($mode == 'manual') {
            $files = collect(Arr::wrap($files))->map(function($path) {
                return [
                    'path' => $path,
                    'content' => $this->filesystem()->get($path),
                ];
            });
        }

        return $files->map(function($file) use ($pattern, $replacement) {
            $file['content'] = preg_replace($pattern, $replacement, $file['content'], -1, $count);
            $file['occurrences'] = $count;

            return $count > 0 ? $file : null;
        })
        ->filter()
        ->values()
        ->map(function($file) {
            $this->filesystem()->put($file['path'], $file['content']);

            return [
                $file['path'], $file['occurrences'],
            ];
        });
    }
}
