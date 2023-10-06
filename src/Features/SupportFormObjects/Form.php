<?php

namespace Livewire\Features\SupportFormObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Drawer\Utils;
use Livewire\Component;

class Form implements Arrayable
{
    protected static $rulesWithField = [
        'required_if',
        'required_unless',
        'accepted_if',
        'declined_if',
        'different',
        'same',
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

    protected static $rulesWithMultipleFields = [
        'required_with',
        'required_with_all',
        'required_without',
        'required_without_all',
        'missing_with',
        'missing_with_all',
    ];

    protected $prefixedAttributesFromRules = [];

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
        $this->component->addRulesFromOutside(function() {
            $rules = [];

            if (method_exists($this, 'rules')) $rules = $this->rules();
            else if (property_exists($this, 'rules')) $rules = $this->rules;

            return $this->getAttributesWithPrefixedKeys($rules, true);
        });
    }

    protected function addValidationAttributesToComponent()
    {
        $this->component->addValidationAttributesFromOutside(function() {
            $validationAttributes = [];

            if (method_exists($this, 'validationAttributes')) $validationAttributes = $this->validationAttributes();
            else if (property_exists($this, 'validationAttributes')) $validationAttributes = $this->validationAttributes;

            return array_merge($this->prefixedAttributesFromRules, $this->getAttributesWithPrefixedKeys($validationAttributes));
        });
    }

    protected function addMessagesToComponent()
    {
        $this->component->addMessagesFromOutside(function() {
            $messages = [];

            if (method_exists($this, 'messages')) $messages = $this->messages();
            else if (property_exists($this, 'messages')) $messages = $this->messages;

            return $this->getAttributesWithPrefixedKeys($messages);
        });
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

    public function fill($values)
    {
        $publicProperties = array_keys($this->all());

        if ($values instanceof Model) {
            $values = $values->toArray();
        }

        foreach ($values as $key => $value) {
            if (in_array(Utils::beforeFirstDot($key), $publicProperties)) {
                data_set($this, $key, $value);
            }
        }
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

    /**
     * This method can be used to prefix rules, attributes, and messages, so if we are processing rules we need to handle
     * it slightly different to ensure that any validation rules that rely on another field still work, by also adding
     * the form property name prefix to the fields specified in the rules.
     */
    protected function getAttributesWithPrefixedKeys($attributes, $areRules = false)
    {
        $attributesWithPrefixedKeys = [];

        foreach ($attributes as $key => $value) {
            $attributesWithPrefixedKeys[$this->propertyName . '.' . $key] = $areRules ? self::getFixedRule($this->propertyName, $value) : $value;
            if ($areRules) {
                $this->prefixedAttributesFromRules[$this->propertyName . '.' . $key] = Str::snake($key, ' ');
                $this->prefixedAttributesFromRules = array_merge($this->prefixedAttributesFromRules, self::getAttributesWithPrefixedKeysFromRule($this->propertyName, $value));
            }
        }

        return $attributesWithPrefixedKeys;
    }

    /**
     * This method contains a list of Laravel validation rules that accept one or more other fields as parameters to
     * run the validation. If the supplied rule is in either list, then the rule is split into it's parts and prefix any
     * field names with the form property name.
     */
    public static function getFixedRule($propertyName, $value)
    {
        $rule = explode(':', $value)[0] ?? null;
        $ruleValue = explode(':', $value)[1] ?? null;

        if ($rule && $ruleValue) {
            if (in_array($rule, self::$rulesWithField)) {
                $field = explode(',', $ruleValue)[0] ?? null;

                if ($field) $value = str_replace($field, $propertyName . '.' . $field, $value);
            } else if (in_array($rule, self::$rulesWithMultipleFields)) {
                $fields = array_unique(explode(',', $ruleValue));

                foreach ($fields as $field) {
                    $value = str_replace($field, $propertyName . '.' . $field, $value);
                }
            }
        }

        return $value;
    }

    public static function getAttributesWithPrefixedKeysFromRule($propertyName, $value)
    {
        $rule = explode(':', $value)[0] ?? null;
        $ruleValue = explode(':', $value)[1] ?? null;

        $attributes = [];

        if ($rule && $ruleValue) {
            if (in_array($rule, self::$rulesWithField)) {
                $field = explode(',', $ruleValue)[0] ?? null;

                $attributes[$propertyName . '.' . $field] = Str::snake($field, ' ');
            } else if (in_array($rule, self::$rulesWithMultipleFields)) {
                $fields = array_unique(explode(',', $ruleValue));

                foreach ($fields as $field) {
                    $attributes[$propertyName . '.' . $field] = Str::snake($field, ' ');
                }
            }
        }

        return $attributes;
    }
}
