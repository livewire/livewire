<?php

namespace LegacyTests\Browser\SupportDynamicValidationAttributes;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public TestFormWithDynamicValidationAttributes $dynamicForm;

    public TestFormWithoutDynamicAttributes $defaultForm;

    public function dynamicValidation()
    {
        $this->dynamicForm->validate();
    }

    public function defaultValidation()
    {
        $this->defaultForm->validate();
    }

    public function render()
    {
        return View::file(__DIR__ . '/view.blade.php');
    }
}
