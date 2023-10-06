<?php

namespace Livewire\Features\SupportValidation;

use Attribute;
use Illuminate\Support\Str;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use Livewire\Form;
use function Livewire\wrap;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class BaseRule extends LivewireAttribute
{
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
        $attributesFromRules = [];
        $componentAttributes = $this->component->getValidationAttributes();

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
                    $attributesFromRules = array_merge($attributesFromRules, Form::getAttributesWithPrefixedKeysFromRule($formPropertyName, $rule));
                }
            } else {
                if (is_array($this->rule)) {
                    $rules[$this->getName()] = [];
                    foreach ($this->rule as $rule) {
                        $rules[$this->getName()][] = Form::getFixedRule($formPropertyName, $rule);
                        $attributesFromRules = array_merge($attributesFromRules, Form::getAttributesWithPrefixedKeysFromRule($formPropertyName, $rule));
                    }
                } else {
                    $rules[$this->getName()] = Form::getFixedRule($formPropertyName, $this->rule);
                    $attributesFromRules = array_merge($attributesFromRules, Form::getAttributesWithPrefixedKeysFromRule($formPropertyName, $this->rule));
                }
            }

            /**
             * This section ensures that any 'form.title' attributes are shortened to 'title' and added
             * to the attribute bag.
             */
            $this->component->addValidationAttributesFromOutside(array_filter($attributesFromRules, fn($key) => !isset($componentAttributes[$key]), ARRAY_FILTER_USE_KEY));

            if(!isset($componentAttributes[$this->getName()])){
                $name = (string)str($this->getName())->after("{$formPropertyName}.");
                $this->component->addValidationAttributesFromOutside([$this->getName() => Str::snake($name,' ')]);
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

        if ($this->attribute) {
            if (is_array($this->attribute)) {
                $this->component->addValidationAttributesFromOutside($this->attribute);
            } else {
                $this->component->addValidationAttributesFromOutside([$this->getName() => $this->attribute]);
            }
        }

        if ($this->as) {
            if (is_array($this->as)) {
                $as = $this->translate
                    ? array_map(fn ($i) => trans($i), $this->as)
                    : $this->as;

                $this->component->addValidationAttributesFromOutside($as);
            } else {
                $this->component->addValidationAttributesFromOutside([$this->getName() => $this->translate ? trans($this->as) : $this->as]);
            }
        }

        if ($this->message) {
            if (is_array($this->message)) {
                $messages = $this->translate
                    ? array_map(fn ($i) => trans($i), $this->message)
                    : $this->message;

                $this->component->addMessagesFromOutside($messages);
            } else {
                // If a single message was provided, apply it to the first given rule.
                // There should have only been one rule provided in this case anyways...
                $rule = head(array_values($rules));

                // In the case of "min:5" or something, we only want "min"...
                $rule = (string) str($rule)->before(':');

                $this->component->addMessagesFromOutside([$this->getName().'.'.$rule => $this->translate ? trans($this->message) : $this->message]);
            }
        }

        $this->component->addRulesFromOutside($rules);
    }

    function update($fullPath, $newValue)
    {
        if ($this->onUpdate === false) return;

        return function () use ($fullPath) {
            wrap($this->component)->validateOnly($fullPath);
        };
    }
}
