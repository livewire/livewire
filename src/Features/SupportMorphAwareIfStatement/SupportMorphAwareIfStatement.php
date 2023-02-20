<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Livewire\ComponentHook;

class SupportMorphAwareIfStatement extends ComponentHook
{
    static function provide()
    {
        $if = '\B@(@?if(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?';
        $endif = '@endif';
        $foreach = '\B@(@?foreach(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?';
        $endforeach = '@endforeach';

        $hasClosingTagBefore = '>[^<]*';
        $hasOpeningTagAfter = '[^>]*<';

        app('livewire')->precompiler('/'.$hasClosingTagBefore.$if.'/x', function ($matches) {
            [$beforeIf, $afterIf] = explode('@if', $matches[0]);

            return $beforeIf.'<!-- __BLOCK__ -->@if'.$afterIf;
        });

        app('livewire')->precompiler('/'.$endif.$hasOpeningTagAfter.'/sm', function ($matches) {
            [$before, $after] = explode('@endif', $matches[0]);

            return $before.'@endif <!-- __ENDBLOCK__ -->'.$after;
        });

        app('livewire')->precompiler('/'.$hasClosingTagBefore.$foreach.'/x', function ($matches) {
            [$beforeIf, $afterIf] = explode('@foreach', $matches[0]);

            return $beforeIf.'<!-- __BLOCK__ -->@foreach'.$afterIf;
        });

        app('livewire')->precompiler('/'.$endforeach.$hasOpeningTagAfter.'/sm', function ($matches) {
            [$before, $after] = explode('@endforeach', $matches[0]);

            return $before.'@endforeach <!-- __ENDBLOCK__ -->'.$after;
        });
    }
}
