<?php

namespace Livewire\Features\SupportBladeAttributes;

use Illuminate\View\ComponentAttributeBag;
use Livewire\ComponentHook;
use Livewire\WireDirective;

class SupportBladeAttributes extends ComponentHook
{
    public static function provide()
    {
        ComponentAttributeBag::macro('wire', function ($name) {
            $entries = head((array) $this->whereStartsWith('wire:'.$name));

            $directive = head(array_keys($entries));
            $value = head(array_values($entries));

            return new WireDirective($name, $directive, $value);
        });
    }
}
