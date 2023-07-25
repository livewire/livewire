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
    }

    public function getComponent() { return $this->component; }
    public function getPropertyName() { return $this->propertyName; }

    public function addValidationRulesToComponent()
    {
        $rules = [];

        if (method_exists($this, 'rules')) $rules = $this->rules();
        else if (property_exists($this, 'rules')) $rules = $this->rules;

        $rulesWithPrefixedKeys = [];

        foreach ($rules as $key => $value) {
            $rulesWithPrefixedKeys[$this->propertyName . '.' . $key] = $value;
        }

        $this->component->addRulesFromOutside($rulesWithPrefixedKeys);
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

    public function reset(...$properties): void
    {
        $properties = array_filter($properties, fn ($property) => array_key_exists($property, $this->all()));

        $reset = [];

        $empty = empty($properties);
        $properties = $properties ?: $this->all();

        foreach ($properties as $key => $value) {
            if (str($value)->startsWith($this->propertyName . '.')) continue;

            $reset[] = $this->propertyName .'.'. ($empty ? $key : $value);
        }

        $this->component->reset(...$reset);
    }

    public function all()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return Utils::getPublicProperties($this);
    }
}
