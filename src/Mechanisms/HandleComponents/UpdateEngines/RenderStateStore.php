<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

interface RenderStateStore
{
    public function get(string $componentId, string $hash): ?string;

    public function put(string $componentId, string $hash, string $html): void;
}
