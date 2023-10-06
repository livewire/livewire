<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Livewire\Livewire;
use Livewire\ComponentHook;

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
                    // Only match directives that are an exact match and not ones that
                    // simply start with the provided directive here...
                    ->map(fn ($directive) => $directive.'(?![a-zA-Z])')
                    // @empty is a special case in that it can be used as a standalone directive
                    // and also within a @forelese statement. We only want to target when it's standalone
                    // by enforcing @empty has an opening parenthesis after it when matching...
                    ->map(fn ($directive) => str($directive)->startsWith('@empty') ? $directive.'[^\S\r\n]*\(' : $directive)
                    ->join('|')
            .')';

            $pattern = '/
                '.$directivesPattern.'  # Blade directives: (@if|@foreach|...)
                (?!                     # Not followed by:
                    [^<]*               # ...
                    (?<![?=-])          # ... (Make sure we don\'t confuse ?>, ->, and =>, with HTML opening tag closings)
                    >                   # A ">" character that isn\'t preceded by a "<" character (meaning it\'s outside of a tag)
                )
            /mUxi';

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

        Livewire::precompiler(function ($entire) use ($generatePattern, $directives) {
            $conditions = \Livewire\invade(app('blade.compiler'))->conditions;

            foreach (array_keys($conditions) as $conditionalDirective) {
                $directives['@'.$conditionalDirective] = '@end'.$conditionalDirective;
            }

            $openings = array_keys($directives);
            $closings = array_values($directives);

            $entire = preg_replace_callback($generatePattern($openings), function ($matches) {
                $original = $matches[0];

                return '<!--[if BLOCK]><![endif]-->'.$original;
            }, $entire) ?? $entire;

            $entire = preg_replace_callback($generatePattern($closings), function ($matches) {
                $original = $matches[0];

                return $original.' <!--[if ENDBLOCK]><![endif]-->';
            }, $entire) ?? $entire;

            return $entire;
        });
    }
}
