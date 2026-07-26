<?php

namespace Livewire\Features\SupportValidation;

use Attribute;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\wrap;

// #[Validate] is a declarative fact: the rules, messages, and validation
// attributes it carries are derived on demand by whoever validates (the
// component, or the form object the property lives on) — nothing is
// registered at a lifecycle moment, so there is no window to miss...
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

    // Whether this attribute belongs to the given validation target: the
    // component itself, or a form object addressed by its property path...
    function providesFor($target)
    {
        if ($target instanceof \Livewire\Component) {
            return $this->getSubTargetClass() === null;
        }

        $path = $target->getPropertyName();

        return $this->getSubTargetClass() !== null
            && ($this->getName() === $path || str($this->getName() ?? '')->startsWith($path.'.'));
    }

    function rules()
    {
        return $this->provisions()['rules'];
    }

    function messages()
    {
        return $this->provisions()['messages'];
    }

    function validationAttributes()
    {
        return $this->provisions()['attributes'];
    }

    protected function provisions()
    {
        $name = $this->getSubTargetClass() ? $this->getSubName() : $this->getName();

        $rules = [];
        $messages = [];
        $validationAttributes = [];

        if (is_null($this->rule)) {
            // Allow "Validate" to be used without a given validation rule. Its purpose is to instead
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
                $validationAttributes = array_merge($validationAttributes, $this->attribute);
            } else {
                $validationAttributes[$name] = $this->attribute;
            }
        }

        if ($this->as) {
            if (is_array($this->as)) {
                $as = $this->translate
                    ? array_map(fn ($i) => trans($i), $this->as)
                    : $this->as;

                $validationAttributes = array_merge($validationAttributes, $as);
            } else {
                $validationAttributes[$name] = $this->translate ? trans($this->as) : $this->as;
            }
        }

        if ($this->message) {
            if (is_array($this->message)) {
                $messages = array_merge($messages, $this->translate
                    ? array_map(fn ($i) => trans($i), $this->message)
                    : $this->message
                );
            } else {
                // If a single message was provided, apply it to the first given rule.
                // There should have only been one rule provided in this case anyways...
                $rule = head(array_values($rules));

                // In the case of "min:5" or something, we only want "min"...
                $rule = (string) str($rule)->before(':');

                $messages[$name.'.'.$rule] = $this->translate ? trans($this->message) : $this->message;
            }
        }

        return ['rules' => $rules, 'messages' => $messages, 'attributes' => $validationAttributes];
    }

    function update($fullPath, $newValue)
    {
        if ($this->onUpdate === false) return;

        return function () use ($fullPath) {
            // If this attribute is added to a "form object", we want to run
            // the validateOnly method on the form object, not the base
            // component. The live instance resolves on demand — by update
            // time it's guaranteed to exist...
            $target = $this->getSubTarget() ?: $this->component;

            // Use the full path so that wildcard rules (e.g. 'items.*') are matched
            // when validating nested properties (e.g. 'items.0'). For form objects,
            // strip the form prefix to get the path relative to the form.
            $name = $this->getSubTargetClass()
                ? $this->getSubName() . str($fullPath)->after($this->getName())
                : $fullPath;

            // Here we have to run the form object validator from the context
            // of the base "wrapped" component so that validation works...
            wrap($this->component)->tap(function () use ($target, $name) {
                $target->validateOnly($name);
            });
        };
    }
}
