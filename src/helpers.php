<?php

namespace Livewire;

use Illuminate\Support\Str;

if (! function_exists('Livewire\str')) {
    function str($string = null)
    {
        if (is_null($string)) return new class {
            public function __call($method, $params) {
                return Str::$method(...$params);
            }
        };

        return Str::of($string);
    }
}
