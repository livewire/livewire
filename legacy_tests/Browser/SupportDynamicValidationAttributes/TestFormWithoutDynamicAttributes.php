<?php

namespace LegacyTests\Browser\SupportDynamicValidationAttributes;

use Livewire\Attributes\Rule;
use Livewire\Form;

class TestFormWithoutDynamicAttributes extends Form
{
    #[Rule('required', as: 'Title', message: 'The :attribute field is must.')]
    public $name;

    #[Rule('required', as: 'Description')]
    public $body;
}
