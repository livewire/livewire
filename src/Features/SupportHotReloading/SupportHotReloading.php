<?php

namespace Livewire\Features\SupportHotReloading;

use Livewire\Mechanisms\HandleComponents\Synthesizers\LivewireSynth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

use function Livewire\on;

class SupportHotReloading
{
    protected $pathsByComponentId = [];

    public function boot()
    {
        return;
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

        on('dehydrate', function ($target, $context) {
            if (! $context->mounting) return;

            $paths = [];

            $paths[] = (new \ReflectionObject($target))->getFileName();

            foreach ($this->pathsByComponentId[$target->getId()] ?? [] as $path) {
                $paths[] = $path;
            }

            $context->addEffect('wiretap', $paths, 'files');

            dd($paths);
        });
    }
}
