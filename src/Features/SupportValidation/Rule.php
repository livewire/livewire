<?php

namespace Livewire\Features\SupportValidation;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\wrap;

#[\Attribute]
class Rule extends LivewireAttribute
{
    function __construct(
        public $rule,
    ) {}

    function boot()
    {
        $this->component->addRuleFromAttribute($this->getName(), $this->rule);
    }

    function update($fullPath, $newValue)
    {
        wrap($this->component)->validateOnly($this->getName(), dataOverrides: [
            $this->getName() => $newValue,
        ]);
    }
}
