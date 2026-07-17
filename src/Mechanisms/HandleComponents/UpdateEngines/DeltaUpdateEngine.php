<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

class DeltaUpdateEngine implements UpdateEngine
{
    public function __construct(protected ResponseTransport $transport) {}

    public function mount(Component $component, string $html, ComponentContext $context): void
    {
        // Advertise only a small protocol/configuration descriptor. The mount
        // HTML is already in the page, but it cannot be used as an exact byte
        // baseline after browser parsing, so the first update still sends full.
        $context->addEffect('renderTransport', $this->transport->configuration());
    }

    public function update(
        Component $component,
        ?string $html,
        array $memo,
        ComponentContext $context,
        array $renderMetadata = [],
    ): void {
        if ($html === null) return;

        // Keep canonical full HTML available through the entire component and
        // response-hook lifecycle. HandleRequests performs the final transport
        // encoding only after extensions have finished mutating the payload.
        $context->addEffect('html', $html);
    }
}
