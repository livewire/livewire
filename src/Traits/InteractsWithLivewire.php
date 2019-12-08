<?php

namespace Livewire\Traits;

trait InteractsWithLivewire
{
    protected $component;

    public function setComponent($component) {
        $this->component = $component;
    }

    public function validationData() {
        return $this->component->getPublicPropertiesDefinedBySubClass();
    }

    public function validateForLivewire() {
        $this->prepareForValidation();

        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        }

        return $this->component->validateWithValidator($this->getValidatorInstance());
    }

    public function validateOnlyForLivewire($field) {
        $this->prepareForValidation();

        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        }

        return $this->component->validateOnlyWithValidator($this->getValidatorInstance(), $field);
    }
}
