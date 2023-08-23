<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Illuminate\Support\Facades\Blade;
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
        $generatePattern = function ($directives) {
            $directivesPattern = '('
                .collect($directives)
                    // Ensure longer directives are in the pattern before shorter ones...
                    ->sortBy(fn ($directive) => strlen($directive), descending: true)
                    ->join('|')
            .')';

            $pattern = '/
                '.$directivesPattern.'        # Blade directive
                (?!                   # Not followed by:
                    [^<]*             # ...
                    (?<![?=-])        # ...
                    >                 # A ">" character, not preceded by a "<" character
                )
            /mUx';

            return $pattern;
        };

        $directives = [
            '@if' => '@endif',
            '@unless' => '@endunless',
            '@error' => '@enderror',
            '@isset' => '@endisset',
            '@empty' => '@endempty',
            '@auth' => '@endauth',
            '@guest' => '@endguest',
            '@switch' => '@endswitch',
            '@foreach' => '@endforeach',
            '@forelse' => '@endforelse',
            '@while' => '@endwhile',
            '@for' => '@endfor',
        ];

        Blade::precompiler(function ($entire) use ($generatePattern, $directives) {
            $openings = array_keys($directives);
            $closings = array_values($directives);

            $entire = preg_replace_callback($generatePattern($openings), function ($matches) {
                $original = $matches[0];

                return '<!-- __BLOCK__ -->'.$original;
            }, $entire);

            $entire = preg_replace_callback($generatePattern($closings), function ($matches) {
                $original = $matches[0];

                return $original.' <!-- __ENDBLOCK__ -->';
            }, $entire);

            return $entire;
        });
    }
}
