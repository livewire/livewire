<?php

namespace Livewire\Features\SupportEnums;

trait BaseDisplayable
{
    // This method name also complies with: \Illuminate\Contracts\Support\DeferringDisplayableValue
    // So that this trait can be used to satisfy that interface for convenience...
    public function resolveDisplayableValue() {
        return $this->display();
    }

    public function display()
    {
        $display = $this->resolveDisplayableValueFromAttribute();

        if ($display === false) {
            $display = $this->resolveDisplayableValueFromCaseName();
        }

        return trans($display);
    }

    protected function resolveDisplayableValueFromAttribute() {
        return BaseDisplay::from($this);
    }

    protected function resolveDisplayableValueFromCaseName() {
        // If no #[Display] attribute is used, fallback to the case name...
        $display = $this->name;

        // Convert something like United_States to "United States"...
        $display = (string) str($display)->replace('_', ' ');

        return $display;
    }
}
