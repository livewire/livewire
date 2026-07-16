<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

class MorphUpdateEngine implements UpdateEngine
{
    public function mount(Component $component, string $html, ComponentContext $context): void
    {
        //
    }

    public function update(
        Component $component,
        ?string $html,
        array $memo,
        ComponentContext $context,
        array $renderMetadata = [],
    ): void {
        if ($html === null) return;

        $context->addEffect('html', $html);
    }
}
