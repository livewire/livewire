<?php

namespace Livewire\Features\SupportDebounce;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class BaseDebounce extends LivewireAttribute
{
    public function __construct(public int|string $duration = 150)
    {
        //
    }

    public function dehydrate($context)
    {
        if (! $context->isMounting()) return;

        $duration = $this->normalizeDuration($this->duration);

        $context->pushEffect('debounce', $duration, $this->getName());
    }

    protected function normalizeDuration(int|string $duration): int
    {
        if (is_int($duration)) {
            return $duration;
        }

        // Support "250ms", "250", "0.25s", etc.
        if (str_ends_with($duration, 'ms')) {
            return (int) $duration;
        }

        if (str_ends_with($duration, 's')) {
            return (int) ((float) $duration * 1000);
        }

        return (int) $duration;
    }
}