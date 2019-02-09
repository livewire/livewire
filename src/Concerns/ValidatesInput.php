<?php

namespace Livewire\Concerns;

use Illuminate\Support\Facades\Validator;

trait ValidatesInput
{
    public function validated($fields)
    {
        $result = [];
        foreach ((array) $fields as $field) {
            $result[$field] = $this->{$field};
        }

        return Validator::make($result, $this->validates)
            ->validate();
    }
}
