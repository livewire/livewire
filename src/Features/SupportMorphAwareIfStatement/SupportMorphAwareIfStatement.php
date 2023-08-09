<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Livewire\ComponentHook;

class SupportMorphAwareIfStatement extends ComponentHook
{
    // @todo: exempt @class and support @error?
    static function provide()
    {
        if (! config('livewire.inject_morph_markers', true)) return;

        static::registerPrecompilers(
            app('livewire')->precompiler(...)
        );
    }

    static function registerPrecompilers($precompile)
    {
        $outsideOfHtmlTag = function ($directive) {
            $htmlTag = '<[^>]*("[^"]*"|\'[^\']*\'|\{\{[^}]*\}\}|@class[^[]*\[(?:[^[\]]++|(?R))*]|@if[^(]*\(([^)]*|->)*\)[^>]*|@foreach[^(]*\(([^)]*|->)*\)[^>]*|[^>])*?>';
            $bladeEchoExpression = '\{\{[^}]*\}\}';
            $bladeParameters = '\([^)]*\)';
            $ignoreIfInsideHtmlTagOrExpression = "({$htmlTag}|{$bladeEchoExpression}|{$bladeParameters})(*SKIP)(*FAIL)|";
            $noOpeningAngleBracketBefore = '(?<!<)';
            $noClosingAngleBracketAfter = '(?!>)';

            return '/'
                .$ignoreIfInsideHtmlTagOrExpression
                .$noOpeningAngleBracketBefore
                .$directive
                .$noClosingAngleBracketAfter
                .'/mU';
        };

        $precompile($outsideOfHtmlTag('@if'), function ($matches) {
            [$before, $after] = explode('@if', $matches[0]);

            return $before.'<!-- __BLOCK__ -->@if'.$after;
        });

        $precompile($outsideOfHtmlTag('@endif'), function ($matches) {
            [$before, $after] = explode('@endif', $matches[0]);

            return $before.'@endif <!-- __ENDBLOCK__ -->'.$after;
        });

        $precompile($outsideOfHtmlTag('@foreach'), function ($matches) {
            [$before, $after] = explode('@foreach', $matches[0]);

            return $before.'<!-- __BLOCK__ -->@foreach'.$after;
        });

        $precompile($outsideOfHtmlTag('@endforeach'), function ($matches) {
            [$before, $after] = explode('@endforeach', $matches[0]);

            return $before.'@endforeach <!-- __ENDBLOCK__ -->'.$after;
        });
    }
}
