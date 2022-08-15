<?php

namespace Livewire;

use Illuminate\Support\ServiceProvider;

class LivewireServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerLivewireSingleton();
    }

    public function boot()
    {
        $this->registerSynthesizers();
        $this->registerMechanisms();
        $this->registerFeatures();
    }

    protected function registerLivewireSingleton()
    {
        $this->app->alias(Manager::class, 'livewire');
        $this->app->singleton(Manager::class);
    }

    protected function registerSynthesizers()
    {
        app('synthetic')->registerSynth([
            \Livewire\Synthesizers\LivewireSynth::class,
        ]);
    }

    protected function registerMechanisms()
    {
        foreach ([
            \Livewire\Mechanisms\BladeDirectives::class,
            \Livewire\Mechanisms\Routes::class,
        ] as $mechanism) {
            (new $mechanism)();
        }
    }

    protected function registerFeatures()
    {
        foreach ([
            \Livewire\Features\SupportReactiveProps::class,
        ] as $feature) {
            (new $feature)();
        }
    }
}
