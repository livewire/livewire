<?php

namespace Livewire\Features\SupportValidation;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\wrap;

#[\Attribute]
class Rule extends LivewireAttribute
{
    // @todo: support custom messages...
    function __construct(
        public $rule,
        protected $attribute = null,
        protected $message = null,
        protected $onUpdate = true,
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

        if ($this->attribute) {
            if (is_array($this->attribute)) {
                $this->component->addValidationAttributesFromOutside($this->attribute);
            } else {
                $this->component->addValidationAttributesFromOutside([$this->getName() => $this->attribute]);
            }
        }

        if ($this->message) {
            if (is_array($this->message)) {
                $this->component->addMessagesFromOutside($this->message);
            } else {
                $this->component->addMessagesFromOutside([$this->getName() => $this->message]);
            }
        }

        $this->component->addRulesFromOutside($rules);
    }

    function update($fullPath, $newValue)
    {
        if ($this->onUpdate === false) return;

        return function () {
            wrap($this->component)->validateOnly($this->getName());
        };
    }
}
