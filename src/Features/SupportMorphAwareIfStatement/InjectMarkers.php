<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

class InjectMarkers
{
    protected $directives = [
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

    function inject($raw)
    {
        $directives = $this->getAllDirectives();

        $allDirectivesRegex = $this->generateDirectivesPattern([...array_keys($directives), ...array_values($directives)]);
        $openingDirectivesRegex = $this->generateDirectivesPattern(array_keys($directives));
        $closingDirectivesRegex = $this->generateDirectivesPattern(array_values($directives));

        // Starting with a raw string like this:
        // <div>@if (true) <div @if (true) @endif></div> @endif</div>

        // We will first get rid of any ">" or "<" characters that AREN'T part of an HTML tag...

        $subject = preg_replace('/
            (?<=[-?=])> # Get rid of any "->", "=>", "?>"
            |
            <(?![a-zA-Z\/]) # Get rid of any "< "
        /mx', '', $raw);

        // Now, we can split this string parts between each blade conditional directive...

        $parts = str($subject)->split('/'.$allDirectivesRegex.'/mx');

        // $parts will now equal:
        // ["<div>"," (true) <div "," (true) ","><\/div> ","<\/div>"]

        // Here we will track weather or not we are inside an HTML opening tag...
        $isInsideATag = false;

        // If we are NOT inside a tag, we can add the index here for later injection...
        $indexesOutsideAnOpeningTag = [];

        foreach ($parts as $index => $part) {
            // We don't care about the last index in this array...
            if ($index === count($parts) - 1) break;

            $openings = substr_count($part, '<');
            $closings = substr_count($part, '>');

            if ($openings > $closings) $isInsideATag = true;
            if ($openings < $closings) $isInsideATag = false;

            if (! $isInsideATag) {
                $indexesOutsideAnOpeningTag[] = $index;
            }
        }

        // Now that we have a list of indexes to inject markers around, we can regex for ALL the directives
        // in the file and only inject comments to ones that are OUTSIDE opening HTML tags...

        $cursor = 0;

        $output = preg_replace_callback('/
            '.$allDirectivesRegex.'
        /mx', function ($matches) use (&$cursor, $indexesOutsideAnOpeningTag, $openingDirectivesRegex, $closingDirectivesRegex) {
            [$original] = $matches;

            $current = $cursor++;

            if (in_array($current, $indexesOutsideAnOpeningTag)) {
                if (str($original)->isMatch('/'.$openingDirectivesRegex.'/')) {
                    return '<!-- __BLOCK__ -->'.$original;
                } else {
                    return $original.' <!-- __ENDBLOCK__ -->';
                }
            }

            return $original;
        }, $raw);

        return $output;
    }

    function getAllDirectives()
    {
        $conditions = \Livewire\invade(app('blade.compiler'))->conditions;

        $allDirectives = $this->directives;

        foreach (array_keys($conditions) as $conditionalDirective) {
            $allDirectives['@'.$conditionalDirective] = '@end'.$conditionalDirective;
        }

        return $allDirectives;
    }

    function generateDirectivesPattern($directives)
    {
        return '('
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
    }
}
