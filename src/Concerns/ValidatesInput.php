<?php

namespace Livewire\Concerns;

use Illuminate\Support\Facades\Validator;

trait ValidatesInput
{
    public function validate($rules)
    {
        $fields = array_keys($rules);

        $result = [];

        foreach ((array) $fields as $field) {
            throw_unless(
                $this->hasProperty($field),
                new \Exception('No property found for validation: [' . $field . ']')
            );

            $result[$field] = $this->{$field};
        }

        return Validator::make($result, array_only($rules, $fields))
            ->validate();
    }
}
