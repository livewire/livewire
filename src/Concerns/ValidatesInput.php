<?php

namespace Livewire\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

trait ValidatesInput
{
    public function validate($rules, $messages = [], $attributes = [])
    {
        $fields = array_keys($rules);

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

        return Validator::make($result, Arr::only($rules, $fields), $messages, $attributes)
            ->validate();
    }
}
