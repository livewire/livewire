<?php

namespace Livewire\Features\SupportFormObjects;

use Illuminate\Contracts\Support\Arrayable;
use Livewire\Component;
use Livewire\Drawer\Utils;

class Form implements Arrayable
{
    function __construct(
        protected Component $component,
        protected $propertyName
    ) {
        $this->addValidationRulesToComponent();
        $this->addValidationAttributesToComponent();
        $this->addMessagesToComponent();
    }

    public function getComponent() { return $this->component; }
    public function getPropertyName() { return $this->propertyName; }

    public function addValidationRulesToComponent()
    {
        $rules = [];

        if (method_exists($this, 'rules')) $rules = $this->rules();
        else if (property_exists($this, 'rules')) $rules = $this->rules;

        $this->component->addRulesFromOutside(
            $this->getAttributesWithPrefixedKeys($rules)
        );
    }

    public function addValidationAttributesToComponent()
    {
        $validationAttributes = [];

        if (method_exists($this, 'validationAttributes')) $validationAttributes = $this->validationAttributes();
        else if (property_exists($this, 'validationAttributes')) $validationAttributes = $this->validationAttributes;

        $this->component->addValidationAttributesFromOutside(
            $this->getAttributesWithPrefixedKeys($validationAttributes)
        );
    }

    public function addMessagesToComponent()
    {
        $messages = [];

        if (method_exists($this, 'messages')) $messages = $this->messages();
        else if (property_exists($this, 'messages')) $messages = $this->messages;

        $this->component->addMessagesFromOutside(
            $this->getAttributesWithPrefixedKeys($messages)
        );
    }

    public function validate()
    {
        $rules = $this->component->getRules();

        $filteredRules = [];

        foreach ($rules as $key => $value) {
            if (! str($key)->startsWith($this->propertyName . '.')) continue;

            $filteredRules[$key] = $value;
        }

        return $this->component->validate($filteredRules)[$this->propertyName];
    }

    public function all()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return Utils::getPublicProperties($this);
    }

    protected function getAttributesWithPrefixedKeys($attributes)
    {
        $attributesWithPrefixedKeys = [];

        foreach ($attributes as $key => $value) {
            $attributesWithPrefixedKeys[$this->propertyName . '.' . $key] = $value;
        }

        return $attributesWithPrefixedKeys;
    }
}
