<?php

namespace Livewire\ComponentConcerns;

use Illuminate\Database\Eloquent\Model;
use Livewire\ObjectPrybar;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Exceptions\MissingRulesPropertyException;

trait ValidatesInput
{
    protected $errorBag;

    public function getErrorBag()
    {
        return $this->errorBag ?? new MessageBag;
    }

    public function addError($name, $message)
    {
        return $this->getErrorBag()->add($name, $message);
    }

    public function setErrorBag($bag)
    {
        return $this->errorBag = $bag instanceof MessageBag
            ? $bag
            : new MessageBag($bag);
    }

    public function resetErrorBag($field = null)
    {
        if (is_null($field)) {
            $this->errorBag = new MessageBag;
        }

        $this->setErrorBag(
            Arr::except($this->getErrorBag()->toArray(), $field)
        );
    }

    public function clearValidation($field = null)
    {
        $this->resetErrorBag($field);
    }

    public function resetValidation($field = null)
    {
        $this->resetErrorBag($field);
    }

    public function errorBagExcept($field)
    {
        return new MessageBag(Arr::except($this->getErrorBag()->toArray(), $field));
    }

    public function rules()
    {
        throw_unless(
            property_exists($this, 'rules'),
            new MissingRulesPropertyException($this->getName())
        );

        return $this->rules;
    }

    public function rulesForModel($name)
    {
        try {
            return collect($this->rules())
                ->filter(function ($value, $key) use ($name) {
                    return $this->beforeFirstDot($key) === $name;
                });
        } catch (MissingRulesPropertyException $ex) {
            return collect();
        }
    }

    public function missingRuleFor($key)
    {
        try {
            return ! in_array($key, array_keys($this->rules()));
        } catch (MissingRulesPropertyException $ex) {
            return true;
        }
    }

    public function validate($rules = null, $messages = [], $attributes = [])
    {
        $rules = is_null($rules) ? $this->rules() : $rules;

        $fields = array_keys($rules);

        $result = $this->getPublicPropertiesDefinedBySubClass();

        foreach ((array) $fields as $field) {
            throw_unless(
                $this->hasProperty($field),
                new \Exception('No property found for validation: ['.$field.']')
            );

            $propertyNameFromValidationField = $this->beforeFirstDot($field);

            $value = $this->getPropertyValue($propertyNameFromValidationField);

            $result[$propertyNameFromValidationField] = $value instanceof Model
                ? $value->toArray() : $value;
        }

        $result = $this->prepareForValidation($result);

        $result = Validator::make($result, Arr::only($rules, $fields), $messages, $attributes)
            ->validate();

        // If the code made it this far, validation passed, so we can clear old failures.
        $this->resetErrorBag();

        return $result;
    }

    public function validateOnly($field, $rules = null, $messages = [], $attributes = [])
    {
        $rules = is_null($rules) ? $this->rules() : $rules;

        $result = $this->getPublicPropertiesDefinedBySubClass();

        throw_unless(
            $this->hasProperty($field),
            new \Exception('No property found for validation: ['.$field.']')
        );

        $propertyNameFromValidationField = $this->beforeFirstDot($field);

        $result[$propertyNameFromValidationField]
            = $this->getPropertyValue($propertyNameFromValidationField);

        try {
            // If the field is "items.0.foo", we should apply the validation rule for "items.*.foo".
            $rulesForField = collect($rules)->filter(function ($rule, $fullFieldKey) use ($field) {
                return Str::is($fullFieldKey, $field);
            })->toArray();

            $result = Validator::make($result, $rulesForField, $messages, $attributes)
                ->validate();
        } catch (ValidationException $e) {
            $messages = $e->validator->getMessageBag();
            $target = new ObjectPrybar($e->validator);

            $target->setProperty(
                'messages',
                $messages->merge(
                    $this->errorBagExcept($field)
                )
            );

            throw $e;
        }

        // If the code made it this far, validation passed, so we can clear old failures.
        $this->resetErrorBag($field);

        return $result;
    }

    protected function prepareForValidation(array $attributes)
    {
        return $attributes;
    }
}
