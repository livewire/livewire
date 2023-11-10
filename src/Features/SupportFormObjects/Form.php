<?php

namespace Livewire\Features\SupportFormObjects;

use function Livewire\invade;
use Livewire\Features\SupportValidation\HandlesValidation;
use Livewire\Drawer\Utils;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Arrayable;

// Form object validation is currently added to the base component.
// Problem: the keys sometimes need a prefix (for displaying) and sometimes don't (for getting data) (form.username vs username)

// Solution A: Isolate form object validation to itself (the form object contains it's own rules and handles it's own validation)
// Problem A: we need to run form object validation when the component runs it's validation with $this->validate();
    // Solution: when base component calls ->validate(), loop through properties, find form objects, and call sub-validate methods
// Problem B: When using `@error()` in templates, currently, it's `@error('form.usernam')`, to keep that, we need to prefix all
// validation rules from the nested form object validation failed bags...

// Solution B: Go through all rule objects from form objects and set columns to scoped names.
// Problem A: people don't always use form objects and the problem still plagues string variations (i.e. "unique:users")

class Form implements Arrayable
{
    use HandlesValidation {
        validate as parentValidate;
        validateOnly as parentValidateOnly;
    }

    function __construct(
        protected Component $component,
        protected $propertyName
    ) {
        //
    }

    public function getName()
    {
        return $this->getComponent()->getName();
    }

    public function validate($rules = null, $messages = [], $attributes = [])
    {
        try {
            return $this->parentValidate($rules, $messages, $attributes);
        } catch (ValidationException $e) {
            $raw = invade($e->validator)->messages->toArray();
            $raw = Arr::prependKeysWith($raw, $this->getPropertyName().'.');
            $errors = new MessageBag($raw);
            invade($e->validator)->messages = $errors;

            throw $e;
        }
    }

    public function validateOnly($field, $rules = null, $messages = [], $attributes = [], $dataOverrides = [])
    {
        try {
            return $this->parentValidateOnly($field, $rules, $messages, $attributes, $dataOverrides);
        } catch (ValidationException $e) {
            $raw = invade($e->validator)->messages->toArray();
            $raw = Arr::prependKeysWith($raw, $this->getPropertyName().'.');
            $errors = new MessageBag($raw);
            invade($e->validator)->messages = $errors;

            throw $e;
        }
    }

    public function getComponent() { return $this->component; }
    public function getPropertyName() { return $this->propertyName; }

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
