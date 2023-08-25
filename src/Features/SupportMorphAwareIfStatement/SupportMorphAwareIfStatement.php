<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Livewire\Livewire;
use Livewire\ComponentHook;
use Illuminate\Support\Facades\Blade;

class SupportMorphAwareIfStatement extends ComponentHook
{
    static function provide()
    {
        if (! config('livewire.inject_morph_markers', true)) return;

        static::registerPrecompilers();
    }

    static function registerPrecompilers()
    {
        $generatePattern = function ($directives) {
            $directivesPattern = '('
                .collect($directives)
                    // Ensure longer directives are in the pattern before shorter ones...
                    ->sortBy(fn ($directive) => strlen($directive), descending: true)
                    ->join('|')
            .')';

            $pattern = '/
                '.$directivesPattern.'  # Blade directives: (@if|@foreach|...)
                (?!                     # Not followed by:
                    [^<]*               # ...
                    (?<![?=-])          # ... (Make sure we don\'t confuse ?>, ->, and =>, with HTML opening tag closings)
                    >                   # A ">" character that isn\'t preceded by a "<" character (meaning it\'s outside of a tag)
                )
            /mUx';

            return $pattern;
        };

        $directives = [
            '@if' => '@endif',
            '@unless' => '@endunless',
            '@error' => '@enderror',
            '@isset' => '@endisset',
            '@auth' => '@endauth',
            '@guest' => '@endguest',
            '@switch' => '@endswitch',
            '@foreach' => '@endforeach',
            '@forelse' => '@endforelse',
            '@while' => '@endwhile',
            '@for' => '@endfor',
        ];

        Livewire::precompiler(function ($entire) use ($generatePattern, $directives) {
            $conditions = \Livewire\invade(app('blade.compiler'))->conditions;

            foreach (array_keys($conditions) as $conditionalDirective) {
                $directives['@'.$conditionalDirective] = '@end'.$conditionalDirective;
            }

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
