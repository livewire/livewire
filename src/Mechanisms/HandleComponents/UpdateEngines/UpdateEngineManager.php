<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Illuminate\Contracts\Container\Container;

class UpdateEngineManager
{
    public function __construct(protected Container $container) {}

    public function current(): UpdateEngine
    {
        return match (config('livewire.update_engine', 'morph')) {
            'morph' => $this->container->make(MorphUpdateEngine::class),
            'delta' => $this->container->make(DeltaUpdateEngine::class),
            default => throw new \InvalidArgumentException(
                'Invalid Livewire update engine. Supported engines: [morph], [delta].'
            ),
        };
    }
}
