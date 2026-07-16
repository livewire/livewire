<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

interface UpdateEngine
{
    public function mount(Component $component, string $html, ComponentContext $context): void;

    public function update(
        Component $component,
        ?string $html,
        array $memo,
        ComponentContext $context,
        array $renderMetadata = [],
    ): void;
}
