<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Livewire\ComponentHook;

class SupportMorphAwareIfStatement extends ComponentHook
{
    static function provide()
    {
        static::registerPrecompilers(
            app('livewire')->precompiler(...)
        );
    }

    static function registerPrecompilers($precompile)
    {
        $isNotInAHtmlTagBefore = '(<[^>]*>|\{\{[^}]*\}\}|\([^)]*\))(*SKIP)(*FAIL)|';
        $isNotInAHtmlTagAfter = '\s*\(';

        $hasOpeningTagAfter = '[^>]*<';

        $precompile('/'. $isNotInAHtmlTagBefore . '@if' . $isNotInAHtmlTagAfter.'/mU', function ($matches) {
            [$before, $after] = explode('@if', $matches[0]);

            return $before.'<!-- __BLOCK__ -->@if'.$after;
        });

        $precompile('/@endif'.$hasOpeningTagAfter.'/sm', function ($matches) {
            [$before, $after] = explode('@endif', $matches[0]);

            return $before.'@endif <!-- __ENDBLOCK__ -->'.$after;
        });

        $precompile('/'.$isNotInAHtmlTagBefore.'@foreach'.$isNotInAHtmlTagAfter.'/mU', function ($matches) {
            [$before, $after] = explode('@foreach', $matches[0]);

            return $before.'<!-- __BLOCK__ -->@foreach'.$after;
        });

        $precompile('/@endforeach'.$hasOpeningTagAfter.'/sm', function ($matches) {
            [$before, $after] = explode('@endforeach', $matches[0]);

            return $before.'@endforeach <!-- __ENDBLOCK__ -->'.$after;
        });
    }
}
