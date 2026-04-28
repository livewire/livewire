<?php

namespace Livewire\Attributes;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MaxNestingDepth extends LivewireAttribute
{
    public function __construct(
        public int $maxDepth
    ) {}
}
