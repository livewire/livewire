<?php

namespace LegacyTests\Browser\SupportDynamicValidationAttributes;

use Livewire\Attributes\Rule;
use Livewire\Form;

class TestFormWithDynamicValidationAttributes extends Form
{
    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $body;

    public function validationAttributes(): array
    {
        return [
            'name' => 'Title',
            'body' => 'Description',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Field :attribute is must.',
        ];
    }
}
