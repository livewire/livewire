<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Features\SupportValidation\HandlesValidation;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use function Livewire\invade;
use Illuminate\Support\Arr;
use Livewire\Drawer\Utils;
use Livewire\Component;

class Form implements Arrayable
{
    use HandlesValidation {
        validate as parentValidate;
        validateOnly as parentValidateOnly;
    }

    function __construct(
        protected Component $component,
        protected $propertyName
    ) {}

    public function getComponent() { return $this->component; }
    public function getPropertyName() { return $this->propertyName; }

    public function validate($rules = null, $messages = [], $attributes = [])
    {
        try {
            return $this->parentValidate($rules, $messages, $attributes);
        } catch (ValidationException $e) {
            invade($e->validator)->messages = $this->prefixErrorBag(invade($e->validator)->messages);
            invade($e->validator)->failedRules = $this->prefixArray(invade($e->validator)->failedRules);

            throw $e;
        }
    }

    public function validateOnly($field, $rules = null, $messages = [], $attributes = [], $dataOverrides = [])
    {
        try {
            return $this->parentValidateOnly($field, $rules, $messages, $attributes, $dataOverrides);
        } catch (ValidationException $e) {
            invade($e->validator)->messages = $this->prefixErrorBag(invade($e->validator)->messages);
            invade($e->validator)->failedRules = $this->prefixArray(invade($e->validator)->failedRules);

            throw $e;
        }
    }

    protected function runSubValidators()
    {
        // This form object IS the sub-validator.
        // Let's skip it...
    }

    protected function prefixErrorBag($bag)
    {
        $raw = $bag->toArray();

        $raw = Arr::prependKeysWith($raw, $this->getPropertyName().'.');

        return new MessageBag($raw);
    }

    protected function prefixArray($array)
    {
        return Arr::prependKeysWith($array, $this->getPropertyName().'.');
    }

    public function addError($key, $message)
    {
        $this->component->addError($this->propertyName . '.' . $key, $message);
    }

    public function resetErrorBag($field = null)
    {
        $fields = (array) $field;

        foreach ($fields as $idx => $field) {
            $fields[$idx] = $this->propertyName . '.' . $field;
        }

        $this->getComponent()->resetErrorBag($fields);
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
}
