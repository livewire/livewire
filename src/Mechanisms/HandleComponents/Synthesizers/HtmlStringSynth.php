<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

use Illuminate\Support\HtmlString;

class HtmlStringSynth extends Synth {
    public static $key = 'html';

    static function match($target) {
        return $target instanceof HtmlString;
    }

    function dehydrate($target) {
        return [$target->__toString(), []];
    }

    function hydrate($value) {
        return new HtmlString($value);
    }
}
