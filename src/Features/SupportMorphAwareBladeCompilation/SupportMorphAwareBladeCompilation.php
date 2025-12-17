<?php

namespace Livewire\Features\SupportMorphAwareBladeCompilation;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;
use Livewire\Livewire;

use function Livewire\on;

class SupportMorphAwareBladeCompilation extends ComponentHook
{
    protected static $shouldInjectConditionalMarkers = false;
    protected static $shouldInjectLoopMarkers = false;

    public static function provide()
    {
        on('flush-state', function () {
            static::$shouldInjectConditionalMarkers = config('livewire.inject_morph_markers', true);
            static::$shouldInjectLoopMarkers = config('livewire.smart_wire_keys', false);
        });

        static::$shouldInjectConditionalMarkers = config('livewire.inject_morph_markers', true);
        static::$shouldInjectLoopMarkers = config('livewire.smart_wire_keys', false);

        if (! static::$shouldInjectConditionalMarkers && ! static::$shouldInjectLoopMarkers) {
            return;
        }

        static::registerPrecompilers();
    }

    public static function registerPrecompilers()
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

        Blade::precompiler(function ($entire) use ($directives) {
            $conditions = \Livewire\invade(app('blade.compiler'))->conditions;

            foreach (array_keys($conditions) as $conditionalDirective) {
                $directives['@'.$conditionalDirective] = '@end'.$conditionalDirective;
            }

            $entire = static::compileDirectives($entire, $directives);

            return $entire;
        });
    }

    /*
     * This method is a modified version of the Blade compiler's `compileStatements` method.
     * It finds all directives in the template, gets the expression if it has parentheses
     * and prefixes the opening directives and suffixes the closing directives.
     */
    public static function compileDirectives($template, $directives)
    {
        $openings = array_keys($directives);
        $closings = array_values($directives);

        $openingDirectivesPattern = static::directivesPattern($openings);
        $closingDirectivesPattern = static::directivesPattern($closings);
        // This is for an `@empty` inside a `@forelse` loop, not `@empty()` conditional directive...
        $loopEmptyDirectivePattern = '/@empty(?!\s*\()/mUxi';

        // First, let's match ALL blade directives on the page, not just conditionals...
        preg_match_all(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( [\S\s]*? ) \))?/x',
            $template,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if (empty($matches[0])) {
            return $template;
        }

        // Find all tags which shouldn't have directives processed inside them...
        $ignoredTags = ['script', 'style'];
        $excludedRanges = static::findIgnoredTagRanges($template, $ignoredTags);

        for ($i = count($matches[0]) - 1; $i >= 0; $i--) {
            $match = [
                $matches[0][$i][0],
                $matches[1][$i][0],
                $matches[2][$i][0],
                $matches[3][$i][0] ?: null,
                $matches[4][$i][0] ?: null,
            ];

            $matchPosition = $matches[0][$i][1];

            // If the blade directive is escaped with an extra `@` then we don't want to process it...
            if (str($match[1])->startsWith('@')) {
                continue;
            }

            // Skip directives inside ignored tags...
            if (static::isInExcludedRange($matchPosition, $excludedRanges)) {
                continue;
            }

            // Here we check to see if we have properly found the closing parenthesis by
            // regex pattern or not, and will recursively continue on to the next ")"
            // then check again until the tokenizer confirms we find the right one.
            while (
                isset($match[4])
                && str($match[0])->endsWith(')')
                && ! static::hasEvenNumberOfParentheses($match[0])
            ) {
                if (($after = str($template)->after($match[0])->toString()) === $template) {
                    break;
                }

                $rest = str($after)->before(')');

                if (
                    isset($matches[0][$i - 1])
                    && str($rest.')')->contains($matches[0][$i - 1][0])
                ) {
                    unset($matches[0][$i - 1]);
                    $i--;
                }

                $match[0] = $match[0].$rest.')';
                $match[3] = $match[3].$rest.')';
                $match[4] = $match[4].$rest;
            }

            // Now we can check to see if the current Blade directive is a conditional,
            // and if so, prefix/suffix it with HTML comment morph markers...
            if (preg_match($openingDirectivesPattern, $match[0])) {
                $template = static::prefixOpeningDirective($match[0], $template, $matchPosition);
            } elseif (preg_match($closingDirectivesPattern, $match[0])) {
                $template = static::suffixClosingDirective($match[0], $template, $matchPosition);
            } elseif (preg_match($loopEmptyDirectivePattern, $match[0])) {
                $template = static::suffixLoopEmptyDirective($match[0], $template, $matchPosition);
            }
        }

        return $template;
    }

    protected static function prefixOpeningDirective($found, $template, $position)
    {
        $foundEscaped = preg_quote($found, '/');

        $livewireCheckOpeningTag = '<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?>';

        $livewireCheckClosingTag = '<?php endif; ?>';

        $prefix = '';

        $suffix = '';

        if (static::$shouldInjectConditionalMarkers) {
            $prefix = '<!--[if BLOCK]><![endif]-->';
        }

        if (static::$shouldInjectLoopMarkers && static::isLoop($found)) {
            $prefix .= '<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?>';

            $suffix .= '<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?>';
        }

        if ($prefix === '' && $suffix === '') {
            return $template;
        }

        if ($prefix !== '') {
            $prefix = $livewireCheckOpeningTag.$prefix.$livewireCheckClosingTag;
        }

        if ($suffix !== '') {
            $suffix = $livewireCheckOpeningTag.$suffix.$livewireCheckClosingTag;
        }

        $prefixEscaped = preg_quote($prefix);

        $suffixEscaped = preg_quote($suffix);

        $pattern = "/(?<!{$prefixEscaped}){$foundEscaped}";

        // If the suffix is not empty, then add it to the pattern...
        if ($suffixEscaped !== '') {
            $pattern .= "(?!{$suffixEscaped})";
        }

        $pattern .= "(?![^<]*(?<![?=-])>)/mUi";

        return static::replaceMatchIfNotInsideAHtmlTag($template, $position, $pattern, $found, $prefix, $suffix);
    }

    protected static function suffixClosingDirective($found, $template, $position)
    {
        // Opening directives can contain a space before the parens, but that causes issues with closing
        // directives. So we will just remove the trailing space if it exists...
        $found = rtrim($found);

        $foundEscaped = preg_quote($found, '/');

        $livewireCheckOpeningTag = '<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?>';

        $livewireCheckClosingTag = '<?php endif; ?>';

        $prefix = '';

        $suffix = '';

        if (static::$shouldInjectConditionalMarkers) {
            $suffix = '<!--[if ENDBLOCK]><![endif]-->';
        }

        if (static::$shouldInjectLoopMarkers && static::isEndLoop($found)) {
            $prefix .= '<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?>';

            $suffix .= '<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?>';
        }

        if ($prefix === '' && $suffix === '') {
            return $template;
        }

        if ($prefix !== '') {
            $prefix = $livewireCheckOpeningTag.$prefix.$livewireCheckClosingTag;
        }

        if ($suffix !== '') {
            $suffix = $livewireCheckOpeningTag.$suffix.$livewireCheckClosingTag;
        }

        $prefixEscaped = preg_quote($prefix);

        $suffixEscaped = preg_quote($suffix);

        $pattern = "/";

        // If the prefix is not empty, then add it to the pattern...
        if ($prefixEscaped !== '') {
            $pattern .= "(?<!{$prefixEscaped})";
        }

        $pattern .= "{$foundEscaped}(?!\w)(?!{$suffixEscaped})(?![^<]*(?<![?=-])>)/mUi";

        return static::replaceMatchIfNotInsideAHtmlTag($template, $position, $pattern, $found, $prefix, $suffix);
    }

    /*
     * This is for an `@empty` inside a `@forelse` loop, not `@empty()` conditional directive. When inside a `@forelse` loop,
     * it is the `@empty` directive that actually closes the loop, not the `@endelseif` directive. So we need to ensure we
     * target the `@empty` directive but not confuse it with the `@empty()` conditional directive...
     */
    protected static function suffixLoopEmptyDirective($found, $template, $position)
    {
        // Opening directives can contain a space before the parens, but that causes issues with closing
        // directives. So we will just remove the trailing space if it exists...
        $found = rtrim($found);

        $foundEscaped = preg_quote($found, '/');

        $livewireCheckOpeningTag = '<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?>';

        $livewireCheckClosingTag = '<?php endif; ?>';

        $prefix = '';

        $suffix = '';

        if (static::$shouldInjectLoopMarkers) {
            $prefix = '<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?>';

            $suffix = '<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?>';
        }

        if ($prefix === '' && $suffix === '') {
            return $template;
        }

        if ($prefix !== '') {
            $prefix = $livewireCheckOpeningTag.$prefix.$livewireCheckClosingTag;
        }

        if ($suffix !== '') {
            $suffix = $livewireCheckOpeningTag.$suffix.$livewireCheckClosingTag;
        }

        $prefixEscaped = preg_quote($prefix);

        $suffixEscaped = preg_quote($suffix);

        $pattern = "/(?<!{$prefixEscaped}){$foundEscaped}(?!\s*\()(?!{$suffixEscaped})(?![^<]*(?<![?=-])>)/mUi";

        return static::replaceMatchIfNotInsideAHtmlTag($template, $position, $pattern, $found, $prefix, $suffix);
    }

    protected static function isLoop($found)
    {
        $loopDirectives = [
            'foreach',
            'forelse',
            'for',
            'while',
        ];

        $pattern = '/@(' . implode('|', $loopDirectives) . ')(?![a-zA-Z])/i';

        return preg_match($pattern, $found);
    }

    protected static function isEndLoop($found)
    {
        $loopDirectives = [
            'endforeach',
            // This `endforelse` should NOT be included here, but it is left here for documentation purposes. The close of a `@forelse` loop is handled by the `@empty` directive...
            // 'endforelse',
            'endfor',
            'endwhile',
        ];

        $pattern = '/@(' . implode('|', $loopDirectives) . ')(?![a-zA-Z])/i';

        return preg_match($pattern, $found);
    }

    protected static function directivesPattern($directives)
    {
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

        // Blade directives: (@if|@foreach|...)
        $pattern = '/'.$directivesPattern.'/mUxi';

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

    protected static function findIgnoredTagRanges(string $template, array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        $ranges = [];

        $escapedTags = array_map('preg_quote', $tags);
        $tagsPattern = implode('|', $escapedTags);

        // Match both opening and closing tags. This handles attributes like `<script type="text/javascript">`...
        preg_match_all(
            '/<('.$tagsPattern.')(?:\s[^>]*)?>|<\/('.$tagsPattern.')>/i',
            $template,
            $tagMatches,
            PREG_OFFSET_CAPTURE
        );

        $stack = [];

        foreach ($tagMatches[0] as $tagMatch) {
            $tag = $tagMatch[0];
            $position = $tagMatch[1];

            // Check if it's an opening tag...
            if (preg_match('/<('.$tagsPattern.')/i', $tag, $typeMatch)) {
                $type = strtolower($typeMatch[1]);
                $stack[] = ['type' => $type, 'start' => $position];
            }
            // Check if it's a closing tag...
            elseif (preg_match('/<\/('.$tagsPattern.')>/i', $tag, $typeMatch)) {
                $type = strtolower($typeMatch[1]);

                // Find the matching opening tag...
                for ($i = count($stack) - 1; $i >= 0; $i--) {
                    if ($stack[$i]['type'] === $type) {
                        $start = $stack[$i]['start'];
                        $end = $position + strlen($tag);
                        $ranges[] = [$start, $end];
                        array_splice($stack, $i, 1);
                        break;
                    }
                }
            }
        }

        return $ranges;
    }

    protected static function isInExcludedRange(int $position, array $ranges): bool
    {
        foreach ($ranges as [$start, $end]) {
            if ($position >= $start && $position < $end) {
                return true;
            }
        }

        return false;
    }

    protected static function replaceMatchIfNotInsideAHtmlTag(string $template, int $position, string $pattern, string $found, string $prefix, string $suffix): string
    {
        // Clamp the match position to the template bounds...
        $templateLength = strlen($template);
        $position = max(0, min($templateLength, (int) $position));

        $before = substr($template, 0, $position);
        $after = substr($template, $position);

        if (! preg_match($pattern, $after, $afterMatch, PREG_OFFSET_CAPTURE) || $afterMatch[0][1] !== 0) {
            return $template;
        }

        // Remove the match from the beginning of the after string...
        $after = substr($after, strlen($afterMatch[0][0]));

        return $before.$prefix.$found.$suffix.$after;
    }
}
