<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Illuminate\Support\Arr;
use Livewire\ComponentHook;
use Livewire\Livewire;

class SupportMorphAwareIfStatement extends ComponentHook
{
    public static function provide()
    {
        if (! config('livewire.inject_morph_markers', true)) {
            return;
        }

        static::registerPrecompilers();
    }

    public static function registerPrecompilers()
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

        Livewire::precompiler(function ($entire) use ($directives) {
            $conditions = \Livewire\invade(app('blade.compiler'))->conditions;

            foreach (array_keys($conditions) as $conditionalDirective) {
                $directives['@'.$conditionalDirective] = '@end'.$conditionalDirective;
            }

            $openings = array_keys($directives);
            $closings = array_values($directives);

            $entire = static::compileStatements($entire, $openings, $closings);

            // ray($generatePattern($openings));
            // ray($generatePattern($closings));

            // $entire = preg_replace_callback($generatePattern($openings), function ($matches) {
            //     $original = $matches[0];

            //     return '<!--[if BLOCK]><![endif]-->'.$original;
            // }, $entire) ?? $entire;

            // $entire = preg_replace_callback($generatePattern($closings), function ($matches) {
            //     $original = $matches[0];

            //     return $original.' <!--[if ENDBLOCK]><![endif]-->';
            // }, $entire) ?? $entire;

            return $entire;
        });
    }

    public static function compileStatements($template, $openings = null, $closings = null)
    {
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

        $openings = $openings ?? array_keys($directives);
        $closings = $closings ?? array_values($directives);

        preg_match_all(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( [\S\s]*? ) \))?/x',
            $template,
            $matches
        );

        // ray($matches);

        $offset = 0;

        for ($i = 0; isset($matches[0][$i]); $i++) {
            $match = [
                $matches[0][$i],
                $matches[1][$i],
                $matches[2][$i],
                $matches[3][$i] ?: null,
                $matches[4][$i] ?: null,
            ];

            // Here we check to see if we have properly found the closing parenthesis by
            // regex pattern or not, and will recursively continue on to the next ")"
            // then check again until the tokenizer confirms we find the right one.
            while (
                isset($match[4])
                && str($match[0])->endsWith(')')
                && ! static::hasEvenNumberOfParentheses($match[0])
            ) {
                if (($after = str($template)->after($match[0])) === $template) {
                    break;
                }

                $rest = str($after)->before(')');

                if (
                    isset($matches[0][$i + 1])
                    && str($rest.')')->contains($matches[0][$i + 1])
                ) {
                    unset($matches[0][$i + 1]);
                    $i++;
                }

                $match[0] = $match[0].$rest.')';
                $match[3] = $match[3].$rest.')';
                $match[4] = $match[4].$rest;
            }

            if (preg_match(static::directivesPattern($openings), $match[0])) { // str($match[0])->startsWith($openings)
                $found = $match[0];

                $foundEscaped = preg_quote($match[0]);

                $prefix = '<!--[if BLOCK]><![endif]-->';

                $prefixEscaped = preg_quote($prefix);

                $foundWithPrefix = $prefix.$found;

                $pattern = "/(?<!{$prefixEscaped}){$foundEscaped}(?![^<]*(?<![?=-])>)/mUi";

                $template = preg_replace($pattern, $foundWithPrefix, $template);
            } elseif (preg_match(static::directivesPattern($closings), $match[0])) { // str($match[0])->startsWith($closings)
                $found = $match[0];

                $foundEscaped = preg_quote($match[0]);

                $suffix = '<!--[if ENDBLOCK]><![endif]-->';

                $suffixEscaped = preg_quote($suffix);

                $foundWithSuffix = $found.$suffix;

                $pattern = "/{$foundEscaped}(?!{$suffixEscaped})(?![^<]*(?<![?=-])>)/mUi";

                $template = preg_replace($pattern, $foundWithSuffix, $template);
            }
        }

        return $template;
    }

    protected static function directivesPattern($directives) {
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

        # Blade directives: (@if|@foreach|...)
        $pattern = '/'.$directivesPattern.'/mUxi';

        ray($pattern);

        return $pattern;
    }

    protected static function hasEvenNumberOfParentheses(string $expression)
    {
        $tokens = token_get_all('<?php '.$expression);

        if (Arr::last($tokens) !== ')') {
            return false;
        }

        $opening = 0;
        $closing = 0;

        foreach ($tokens as $token) {
            if ($token == ')') {
                $closing++;
            } elseif ($token == '(') {
                $opening++;
            }
        }

        return $opening === $closing;
    }
}
