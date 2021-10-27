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

if (! function_exists('Livewire\js')) {
    /**
     * base64 encode an expression and compile it into 'JSON.parse(atob($base64))' string.
     *
     * @param $expression
     * @param int|null $options
     * @param int $depth
     * @return string
     */
    function js($expression, $options = null, $depth = 512): string
    {
        if (is_object($expression) || is_array($expression)) {
            $base64 = base64_encode(json_encode($expression, $options, $depth));
            return "JSON.parse(atob('$base64'))";
        }
        if (is_string($expression)) {
            $string = str_replace("'", "\'", $expression);
            return "'$string'";
        }
        return json_encode($expression, $options, $depth);
    }
}
