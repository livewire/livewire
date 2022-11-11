<?php

namespace Livewire\Features\SupportHotReloading;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class SupportHotReloading
{
    protected $pathsByComponentId = [];

    public function boot()
    {
        if (! app()->environment('local') || ! config('app.debug')) return;

        app('livewire')->enableJsFeature('hot-reloading');

        on('view:compile', function ($component, $path) {
            if (! isset($this->pathsByComponentId[$component->getId()])) {
                $this->pathsByComponentId[$component->getId()] = [];
            }

            if (! in_array($path, $this->pathsByComponentId[$component->getId()])) {
                $this->pathsByComponentId[$component->getId()][] = $path;
            }
        });

        on('dehydrate', function ($synth, $target, $context) {
            if (! $context->initial) return;
            if (! $synth instanceof LivewireSynth) return;

            $path = (new \ReflectionObject($target))->getFileName();

            return function ($stuff) use ($target, $context, $path) {
                $context->addEffect('hotReload', [
                    $path,
                    ...array_values($this->pathsByComponentId[$target->getId()] ?? [])
                ]);

                return $stuff;
            };
        });

        Route::get('/livewire/hot-reload', function () {
            return response()->stream(function () {
                $filesByTime = [];

                while(true) {
                    foreach ([
                        ...File::allFiles(base_path()),
                        ...File::allFiles(resource_path())
                    ] as $file) {
                        $time = filemtime((string) $file);

                        if (isset($filesByTime[(string) $file]) && $filesByTime[(string) $file] !== $time) {
                            echo 'data: ' . json_encode(['file' => (string) $file]) . "\n\n";
                            ob_flush();
                            flush();
                        }

                        $filesByTime[(string) $file] = $time;
                    }

                    sleep(.25);
                    echo 'data: ' . json_encode(['ping']) . "\n\n";
                    ob_flush();
                    flush();
                }
            }, 200, [
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'text/event-stream',
                'X-Accel-Buffering' => 'no',
            ]);
        });
    }
}
