<?php

namespace Livewire\Features;

class SupportMorphAwareIfStatement
{
    function boot()
    {
        $if = '\B@(@?if(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?';
        $endif = '@endif';

        $hasClosingTagBefore = '>[^<]*';
        $hasOpeningTagAfter = '[^>]*<';

        app('livewire')->precompiler('/'.$hasClosingTagBefore.$if.'/x', function ($matches) {
            [$beforeIf, $afterIf] = explode('@if', $matches[0]);

            return $beforeIf.'<!-- __IF__ -->@if'.$afterIf;
        });

        app('livewire')->precompiler('/'.$endif.$hasOpeningTagAfter.'/sm', function ($matches) {
            [$before, $after] = explode('@endif', $matches[0]);

            return $before.'@endif <!-- __ENDIF__ -->'.$after;
        });
    }
}
