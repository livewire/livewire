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
        $this->addValidationAttributesToComponent();
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
            $this->getAttributesWithPrefixedKeys($rules, true)
        );
    }

    protected function addValidationAttributesToComponent()
    {
        $validationAttributes = [];

        if (method_exists($this, 'validationAttributes')) $validationAttributes = $this->validationAttributes();
        else if (property_exists($this, 'validationAttributes')) $validationAttributes = $this->validationAttributes;

        $this->component->addValidationAttributesFromOutside(
            $this->getAttributesWithPrefixedKeys($validationAttributes)
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

    public function only($properties)
    {
        $results = [];

        foreach (is_array($properties) ? $properties : func_get_args() as $property) {
            $results[$property] = $this->hasProperty($property) ? $this->getPropertyValue($property) : null;
        }

        return $results;
    }

    public function except($properties)
    {
        $properties = is_array($properties) ? $properties : func_get_args();

        return array_diff_key($this->all(), array_flip($properties));
    }

    public function hasProperty($prop)
    {
        return property_exists($this, Utils::beforeFirstDot($prop));
    }

    public function getPropertyValue($name)
    {
        $value = $this->{Utils::beforeFirstDot($name)};

        if (Utils::containsDots($name)) {
            return data_get($value, Utils::afterFirstDot($name));
        }

        return $value;
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

    protected function getAttributesWithPrefixedKeys($attributes, $areRules = false)
    {
        $attributesWithPrefixedKeys = [];

        foreach ($attributes as $key => $value) {
            $attributesWithPrefixedKeys[$this->propertyName . '.' . $key] = $areRules ? self::getFixedRule($this->propertyName, $value) : $value;
        }

        return $attributesWithPrefixedKeys;
    }

    public static function getFixedRule($propertyName, $value){
        $rulesWithField = [
            'required_if',
            'required_unless',
            'accepted_if',
            'declined_if',
            'different',
            'exclude_if',
            'exclude_unless',
            'exclude_with',
            'exclude_without',
            'gt',
            'gte',
            'lt',
            'lte',
            'in_array',
            'missing_if',
            'missing_unless',
            'prohibited_if',
            'prohibited_unless',
            'prohibits',
        ];
        $rulesWithMultipleFields = [
            'required_with',
            'required_with_all',
            'required_without',
            'required_without_all',
            'missing_with',
            'missing_with_all',
        ];

        $rule = explode(':', $value)[0] ?? null;
        $ruleValue = explode(':', $value)[1] ?? null;

        if ($rule && $ruleValue) {
            if (in_array($rule, $rulesWithField)) {
                $field = explode(',', $ruleValue)[0] ?? null;
                if ($field) $value = str_replace($field, $propertyName . '.' . $field, $value);
            } else if (in_array($rule, $rulesWithMultipleFields)) {
                $fields = array_unique(explode(',', $ruleValue));
                foreach ($fields as $field) {
                    $value = str_replace($field, $propertyName . '.' . $field, $value);
                }
            }
        }

        return $value;
    }
}
