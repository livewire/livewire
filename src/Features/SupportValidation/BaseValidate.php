<?php

namespace Livewire\Features\SupportValidation;

use Attribute;
use Illuminate\Auth\Events\Validated;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\wrap;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class BaseValidate extends LivewireAttribute
{
    function __construct(
        public $rule = null,
        protected $attribute = null,
        protected $as = null,
        protected $message = null,
        protected $onUpdate = true,
        protected bool $translate = true
    ) {}

    function boot()
    {
        $rules = [];

        if (is_null($this->rule)) {
            // Allow "Rule" to be used without a given validation rule. It's purpose is to instead
            // trigger validation on property updates...
        } elseif (is_array($this->rule) && count($this->rule) > 0 && ! is_numeric(array_keys($this->rule)[0])) {
            // Support setting rules by key-value for this and other properties:
            // For example, #[Validate(['foo' => 'required', 'foo.*' => 'required'])]
            $rules = $this->rule;
        } else {
            $rules[$this->getSubName()] = $this->rule;
        }

        if ($this->attribute) {
            if (is_array($this->attribute)) {
                $target = $this->subTarget ?? $this->component;
                $target->addValidationAttributesFromOutside($this->attribute);
            } else {
                $target = $this->subTarget ?? $this->component;
                $target->addValidationAttributesFromOutside([$this->getSubName() => $this->attribute]);
            }
        }

        if ($this->as) {
            if (is_array($this->as)) {
                $as = $this->translate
                    ? array_map(fn ($i) => trans($i), $this->as)
                    : $this->as;

                    $target = $this->subTarget ?? $this->component;
                $target->addValidationAttributesFromOutside($as);
            } else {
                $target = $this->subTarget ?? $this->component;
                $target->addValidationAttributesFromOutside([$this->getSubName() => $this->translate ? trans($this->as) : $this->as]);
            }
        }

        if ($this->message) {
            if (is_array($this->message)) {
                $messages = $this->translate
                    ? array_map(fn ($i) => trans($i), $this->message)
                    : $this->message;

                $target = $this->subTarget ?? $this->component;
                $target->addMessagesFromOutside($messages);
            } else {
                // If a single message was provided, apply it to the first given rule.
                // There should have only been one rule provided in this case anyways...
                $rule = head(array_values($rules));

                // In the case of "min:5" or something, we only want "min"...
                $rule = (string) str($rule)->before(':');

                $target = $this->subTarget ?? $this->component;
                $target->addMessagesFromOutside([$this->getSubName().'.'.$rule => $this->translate ? trans($this->message) : $this->message]);
            }
        }

        $target = $this->subTarget ?? $this->component;
        $target->addRulesFromOutside($rules);
    }

    function update($fullPath, $newValue)
    {
        if ($this->onUpdate === false) return;

        return function () {
            wrap($this->component)->noop(function () {
                ($this->subTarget ?? $this->component)->validateOnly($this->getSubName());
            });
        };
    }
}
