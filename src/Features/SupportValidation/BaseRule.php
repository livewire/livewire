<?php

namespace Livewire\Features\SupportValidation;

use Attribute;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use Livewire\Form;
use function Livewire\wrap;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class BaseRule extends LivewireAttribute
{
    // @todo: support custom messages...
    function __construct(
        public $rule,
        protected $attribute = null,
        protected $as = null,
        protected $message = null,
        protected $onUpdate = true,
        protected bool $translate = true
    ) {}

    function boot()
    {
        /**
         * Determine if this attribute is being used in a FormObject and what the property name
         * is that the FormObject is assigned to.
         */
        $formPropertyName = null;
        if (str_contains(str($this->getName()), '.')) {
            $propertyName = explode('.', str($this->getName()))[0];
            if(isset($this->component->$propertyName) && is_subclass_of($this->component->$propertyName, Form::class)){
                $formPropertyName = $propertyName;
            }
        }

        $rules = [];

        /**
         * If the rule attribute is being used inside a FormObject, process the rules to ensure
         * that any rules with dependant fields get the field names prefixed with the name of
         * the property the FormObject is assigned to.
         */
        if ($formPropertyName) {
            // Support setting rules by key-value for this and other properties:
            // For example, #[Rule(['foo' => 'required', 'foo.*' => 'required'])]
            if (is_array($this->rule) && count($this->rule) > 0 && ! is_numeric(array_keys($this->rule)[0])) {
                foreach ($this->rule as $field => $rule) {
                    if (!str_starts_with($field, "{$formPropertyName}.")) $field = "{$formPropertyName}.{$field}";
                    $rules[$field] = Form::getFixedRule($formPropertyName, $rule);
                }
            } else {
                if (is_array($this->rule)) {
                    $rules[$this->getName()] = [];
                    foreach ($this->rule as $rule) {
                        $rules[$this->getName()][] = Form::getFixedRule($formPropertyName, $rule);
                    }
                } else {
                    $rules[$this->getName()] = Form::getFixedRule($formPropertyName, $this->rule);
                }
            }
        } else {
            // Support setting rules by key-value for this and other properties:
            // For example, #[Rule(['foo' => 'required', 'foo.*' => 'required'])]
            if (is_array($this->rule) && count($this->rule) > 0 && ! is_numeric(array_keys($this->rule)[0])) {
                $rules = $this->rule;
            } else {
                $rules[$this->getName()] = $this->rule;
            }
        }

        /**
         * This section ensures that any 'form.title' attributes are shortened to 'title' and added
         * to the attribute bag.
         *
         * @todo: This needs to be reenabled and updated to also get the fields from the dependant rules, and do
         * the same thing. And fix the custom attributes method being overridden.
         */
        // if ($formPropertyName) {
        //     $name = (string) str($this->getName())->after("{$formPropertyName}.");

        //     $this->component->addValidationAttributesFromOutside([$this->getName() => $name]);
        // }

        if ($this->attribute) {
            if (is_array($this->attribute)) {
                $this->component->addValidationAttributesFromOutside($this->attribute);
            } else {
                $this->component->addValidationAttributesFromOutside([$this->getName() => $this->attribute]);
            }
        }

        if ($this->as) {
            if (is_array($this->as)) {
                $this->component->addValidationAttributesFromOutside($this->translate ? trans($this->as) : $this->as);
            } else {
                $this->component->addValidationAttributesFromOutside([$this->getName() => $this->translate ? trans($this->as) : $this->as]);
            }
        }

        if ($this->message) {
            if (is_array($this->message)) {
                $this->component->addMessagesFromOutside($this->translate ? trans($this->message) : $this->message);
            } else {
                $this->component->addMessagesFromOutside([$this->getName() => $this->translate ? trans($this->message) : $this->message]);
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
