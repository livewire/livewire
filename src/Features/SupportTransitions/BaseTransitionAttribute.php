<?php

namespace Livewire\Features\SupportTransitions;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class BaseTransitionAttribute extends LivewireAttribute
{
    public function __construct(
        public ?string $type = null,
        public bool $skip = false,
    ) {}

    function call()
    {
        if ($this->type) {
            $this->storeSet('transitionType', $this->type);
        }

        if ($this->skip) {
            $this->storeSet('transitionSkip', true);
        }
    }
}
