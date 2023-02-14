<?php

namespace Livewire\Features\SupportBladeAttributes;

use Livewire\WireDirective;
use Illuminate\View\ComponentAttributeBag;
use Livewire\ComponentHook;

class SupportBladeAttributes extends ComponentHook
{
    static function provide()
    {
        ComponentAttributeBag::macro('wire', function ($name) {
            $entries = head((array) $this->whereStartsWith('wire:'.$name));

            $directive = head(array_keys($entries));
            $value = head(array_values($entries));

            return new WireDirective($name, $directive, $value);
        });
    }
}
