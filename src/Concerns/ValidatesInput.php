<?php

namespace Livewire\Concerns;

use Illuminate\Support\Facades\Validator;

trait ValidatesInput
{
    protected $validates;

    public function validate($fields = null)
    {
        $fields = $fields ?: array_keys($this->validates);

        $result = [];

        foreach ((array) $fields as $field) {
            throw_unless(
                $this->hasProperty($field),
                new \Exception('No property found for validation: [' . $field . ']')
            );

            $result[$field] = $this->{$field};
        }

        return Validator::make($result, array_only($this->validates, $fields))
            ->validate();
    }
}
