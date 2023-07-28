<?php

namespace Livewire\Features\SupportFormObjects;

use Illuminate\Contracts\Support\Arrayable;
use Livewire\Drawer\Utils;
use Livewire\Component;

class Form implements Arrayable
{
    function __construct(
        protected Component $component,
        protected $propertyName
    ) {
        $this->addValidationRulesToComponent();
        $this->addAttributesToComponent();
        $this->addMessagesToComponent();
    }

    public function getComponent() { return $this->component; }
    public function getPropertyName() { return $this->propertyName; }

    protected function addValidationRulesToComponent()
    {
        $rules = [];

        if (method_exists($this, 'rules')) $rules = $this->rules();
        else if (property_exists($this, 'rules')) $rules = $this->rules;

        $this->component->addRulesFromOutside(
            $this->getAttributesWithPrefixedKeys($rules)
        );
    }

    protected function addAttributesToComponent()
    {
        $attributes = [];

        if (method_exists($this, 'attributes')) $attributes = $this->attributes();
        else if (property_exists($this, 'attributes')) $attributes = $this->attributes;

        $this->component->addValidationAttributesFromOutside(
            $this->getAttributesWithPrefixedKeys($attributes)
        );
    }

    protected function addMessagesToComponent()
    {
        $messages = [];

        if (method_exists($this, 'messages')) $messages = $this->messages();
        else if (property_exists($this, 'messages')) $messages = $this->messages;

        $this->component->addMessagesFromOutside(
            $this->getAttributesWithPrefixedKeys($messages)
        );
    }

    public function addError($key, $message)
    {
        $this->component->addError($this->propertyName . '.' . $key, $message);
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

    public function reset(...$properties)
    {
        $properties = count($properties) && is_array($properties[0])
            ? $properties[0]
            : $properties;

        if (empty($properties)) $properties = array_keys($this->all());

        $freshInstance = new static($this->getComponent(), $this->getPropertyName());

        foreach ($properties as $property) {
            data_set($this, $property, data_get($freshInstance, $property));
        }
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
