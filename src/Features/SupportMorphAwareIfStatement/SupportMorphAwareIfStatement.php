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

            // Here's the regex strategy:
            // Look for blade directives and only match ones that are OUTSIDE HTML tags.
            // This is how we can know that a directive is INSIDE a tag:
            // - find the tag
            // - look ahead for the next "<" character
            // - if we found a ">" character while looking ahead
            // - we know we are OUTSIDE a tag...
            //
            // There are two exceptions we have to account for:
            // 1) PHP has ->, =>, and ? > that we have make an exception for
            // 2) logical comparisons like "1 < 2" also have to be accounted for...
            //
            // We get around these exceptions by using a negative look behind for "?,=,-"
            // and ONLY matching "<" characters that have either "/" or a letter after them
            // (because all "<" characters in HTML either are part of a closing tag or have a tag name next ot them)

            $pattern = '/
                '.$directivesPattern.'            # Blade directives: (@if|@foreach|...)
                (?!                               # NOT followed by:
                    (
                        [^<]                      # All non "<" characters
                        |                         # OR
                        (?!<[a-zA-Z\/])<          # A "<" character without a forward slash or letter after it
                    )*                            # As many characters as it can until ">" is reached
                    (?<![?=-])                    # Ignore "?>", "->", and "=>"
                    >                             # A ">" character
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
