<?php

namespace Livewire\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

trait ValidatesInput
{
    public function validate($rules, $messages = [], $attributes = [])
    {
        $fields = array_keys($rules);

        $result = $this->getAllPublicPropertiesDefinedBySubClass();

        foreach ((array) $fields as $field) {
            throw_unless(
                $this->hasProperty($field),
                new \Exception('No property found for validation: [' . $field . ']')
            );

            $result[$this->beforeFirstDot($field)] = $this->getPropertyValue($field);
        }

        return Validator::make($result, Arr::only($rules, $fields), $messages, $attributes)
            ->validate();
    }
}
