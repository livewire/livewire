<?php

namespace Livewire;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
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
            \Livewire\Mechanisms\CompileLivewireTags::class,
            \Livewire\Mechanisms\BladeDirectives::class,
            \Livewire\Mechanisms\Routes::class,
        ] as $mechanism) {
            (new $mechanism)();
        }
    }

    protected function registerFeatures()
    {
        foreach ([
            // \Livewire\Features\SupportReactiveProps::class,
            \Livewire\Features\SupportWireModelingNestedComponents::class,
        ] as $feature) {
            (new $feature)();
        }
    }
}
