<?php

namespace Livewire\Features\SupportBladeAttributes;

use Livewire\WireDirective;
use Illuminate\View\ComponentAttributeBag;

class SupportBladeAttributes
{
    function boot()
    {
        ComponentAttributeBag::macro('wire', function ($name) {
            $entries = head((array) $this->whereStartsWith('wire:'.$name));

            $directive = head(array_keys($entries));
            $value = head(array_values($entries));

            return new WireDirective($name, $directive, $value);
        });
    }
}
