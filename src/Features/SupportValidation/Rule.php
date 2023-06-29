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
        $rules = [];

        // Support setting rules by key-value for this and other properties:
        // For example, #[Rule(['foo' => 'required', 'foo.*' => 'required'])]
        if (is_array($this->rule) && count($this->rule) > 0 && ! is_numeric(array_keys($this->rule)[0])) {
            $rules = $this->rule;
        } else {
            $rules[$this->getName()] = $this->rule;
        }

        $this->component->addRulesFromOutside($rules);
    }

    function update($fullPath, $newValue)
    {
        return function () {
            wrap($this->component)->validateOnly($this->getName());
        };
    }
}
