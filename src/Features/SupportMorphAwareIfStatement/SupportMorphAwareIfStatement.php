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
        $if = '\B@(@?if(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?';
        $endif = '@endif';
        $foreach = '\B@(@?foreach(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?';
        $endforeach = '@endforeach';

        $hasOpeningTagBefore = '<[^>]*';
        $hasOpeningTagAfter = '[^>]*<';
        $openingTagIsInBladeExpression = '{(?>{|!!)[^}]*<[^}]*(?>}|!!)}';

        $precompile('/(?:'.$hasOpeningTagBefore.$if.')(*SKIP)(*F)|'.$if.'/x', function ($matches) {
            [$beforeIf, $afterIf] = explode('@if', $matches[0]);

            return $beforeIf.'<!-- __BLOCK__ -->@if'.$afterIf;
        });

        $precompile('/'.$endif.$hasOpeningTagAfter.'/sm', function ($matches) {
            [$before, $after] = explode('@endif', $matches[0]);

            return $before.'@endif <!-- __ENDBLOCK__ -->'.$after;
        });

        $precompile('/(?:'.$hasOpeningTagBefore.$foreach.')(*SKIP)(*F)|'.$foreach.'/x', function ($matches) {
            [$beforeIf, $afterIf] = explode('@foreach', $matches[0]);

            return $beforeIf.'<!-- __BLOCK__ -->@foreach'.$afterIf;
        });

        $precompile('/'.$endforeach.$hasOpeningTagAfter.'/sm', function ($matches) {
            [$before, $after] = explode('@endforeach', $matches[0]);

            return $before.'@endforeach <!-- __ENDBLOCK__ -->'.$after;
        });
    }
}
