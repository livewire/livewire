<?php

namespace Livewire\Features\SupportTransitions;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

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
            store($this->component)->set('transitionType', $this->type);
        }

        if ($this->skip) {
            store($this->component)->set('transitionSkip', true);
        }
    }
}
