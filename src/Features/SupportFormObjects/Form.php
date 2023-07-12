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

    public function all()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return Utils::getPublicProperties($this);
    }
}
