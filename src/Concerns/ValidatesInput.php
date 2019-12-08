<?php

namespace Livewire\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\ObjectPrybar;
use Livewire\Routing\Redirector;

trait ValidatesInput
{
    protected $errorBag;

    public function getErrorBag()
    {
        return $this->errorBag ?? new MessageBag;
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
            Arr::except($this->errorBag->toArray(), $field)
        );
    }

    public function resetValidation($field = null)
    {
        $this->resetErrorBag($field);
    }

    public function errorBagExcept($field)
    {
        return new MessageBag(Arr::except($this->errorBag->toArray(), $field));
    }

    public function validate($rules, $messages = [], $attributes = [])
    {
        if (is_string($rules)) {
            return $this
                ->createFormRequestInstance($rules)
                ->validateForLivewire();
        }

        return $this->validateWithValidator(
            Validator::make(
                $this->getPublicPropertiesDefinedBySubClass(),
                Arr::only($rules, array_keys($rules)),
                $messages,
                $attributes
            )
        );
    }

    public function validateWithValidator($validator)
    {
        $fields = array_keys($validator->getRules());

        $result = $this->getPublicPropertiesDefinedBySubClass();

        foreach ((array) $fields as $field) {
            throw_unless(
                $this->hasProperty($field),
                new \Exception('No property found for validation: ['.$field.']')
            );

            $propertyNameFromValidationField = $this->beforeFirstDot($field);

            $result[$propertyNameFromValidationField]
                = $this->getPropertyValue($propertyNameFromValidationField);
        }

        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());
        } else {
            // If the code made it this far, validation passed, so we can clear old failures.
            $this->resetErrorBag();
        }

        return $validator->validate();
    }

    public function validateOnly($field, $rules, $messages = [], $attributes = [])
    {
        if (is_string($rules)) {
            return $this
                ->createFormRequestInstance($rules)
                ->validateOnlyForLivewire($field);
        }

        return $this->validateOnlyWithValidator(
            Validator::make(
                $this->getPublicPropertiesDefinedBySubClass(),
                Arr::only($rules, $field),
                $messages,
                $attributes
            ),
            $field
        );
    }

    public function validateOnlyWithValidator($validator, $field) {
        $result = $this->getPublicPropertiesDefinedBySubClass();

        throw_unless(
            $this->hasProperty($field),
            new \Exception('No property found for validation: ['.$field.']')
        );

        $propertyNameFromValidationField = $this->beforeFirstDot($field);

        $result[$propertyNameFromValidationField]
            = $this->getPropertyValue($propertyNameFromValidationField);

        try {
            $result = $validator->validate();
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

    protected function createFormRequestInstance($class)
    {
        $formRequest = new $class();
        $formRequest->setContainer(app());
        $formRequest->setRedirector(app()->make(Redirector::class));
        $formRequest->setComponent($this);

        return $formRequest;
    }
}
