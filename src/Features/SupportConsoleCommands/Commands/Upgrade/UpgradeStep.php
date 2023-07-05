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

    public function patternReplacement($pattern, $replacement, $directory = 'resources/views')
    {
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
    }
}
