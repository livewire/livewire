<?php

namespace Livewire\Features\SupportDebounce;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class BaseDebounce extends LivewireAttribute
{
    public function __construct(public int|string|null $duration = null)
    {
        //
    }

    public function dehydrate($context)
    {
        if (! $context->isMounting()) return;

        $duration = $this->duration !== null
            ? $this->normalizeDuration($this->duration)
            : true;

        $context->pushEffect('debounce', $duration, $this->getName());
    }

    protected function normalizeDuration(int|string $duration): int
    {
        if (is_int($duration)) {
            return max(0, $duration);
        }

        $duration = strtolower(trim($duration));

        if (str_ends_with($duration, 'ms')) {
            return $this->normalizeNumber(substr($duration, 0, -2));
        }

        if (str_ends_with($duration, 's')) {
            return $this->normalizeNumber(substr($duration, 0, -1), 1000);
        }

        return $this->normalizeNumber($duration);
    }

    protected function normalizeNumber(string $value, int $multiplier = 1): int
    {
        if ($value === '' || ! is_numeric($value)) {
            return 150;
        }

        return max(0, (int) ((float) $value * $multiplier));
    }
}