<?php

namespace Livewire\Features\SupportConsoleCommands\Commands\Upgrade;

use Illuminate\Support\Arr;
use Closure;
use Illuminate\Console\Command;
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

    public function manualUpgradeWarning($console, $warning, $before, $after)
    {
        $console->newLine();
        $console->error($warning);
        $console->newLine();

        $this->beforeAfterView(
            console: $console,
            before: $before,
            after: $after,
        );

        $console->confirm('Ready to continue?');
    }

    public function beforeAfterView($console, $before, $after, $title = 'Before/After example')
    {
        $console->table(
            [$title],
            [
                array_map(fn($line) => "<fg=red>- {$line}</>", Arr::wrap($before)),
                array_map(fn($line) => "<fg=green>+ {$line}</>", Arr::wrap($after))
            ],
        );
    }

    public function interactiveReplacement(Command $console, $title, $before, $after, $pattern, $replacement, $directories = ['resources/views'])
    {
        $console->newLine(4);
        $console->line("<fg=#FB70A9;bg=black;options=bold,reverse> {$title} </>");
        $console->newLine();
        $console->line('Please review the example below and confirm if you would like to apply this change.');
        $console->newLine();

        $this->beforeAfterView($console, $before, $after);

        $confirm = $console->confirm('Would you like to apply these changes?', true);

        if ($confirm) {
            $console->newLine();

            $replacements = $this->patternReplacement($pattern, $replacement, $directories);

            if($replacements->isEmpty())
            {
                $console->line('No occurrences of were found.');
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
            if($replacement instanceof Closure) {
                $file['content'] = preg_replace_callback($pattern, $replacement, $file['content'], -1, $count);
            } else {
                $file['content'] = preg_replace($pattern, $replacement, $file['content'], -1, $count);
            }
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
