<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Exceptions\RootTagMissingFromViewException;

class AddAttributesToRootTagOfHtml
{
    public function __invoke($dom, $data)
    {
        $attributesFormattedForHtmlElement = collect($data)
            ->mapWithKeys(function ($value, $key) {
                return ["wire:{$key}" => $this->escapeStringForHtml($value)];
            })->map(function ($value, $key) {
                return sprintf('%s="%s"', $key, $value);
            })->implode(' ');

        preg_match('/<([a-zA-Z0-9\-]*)/', $dom, $matches, PREG_OFFSET_CAPTURE);

        throw_unless(
            count($matches),
            new RootTagMissingFromViewException
        );

        $tagName = $matches[1][0];
        $lengthOfTagName = strlen($tagName);
        $positionOfFirstCharacterInTagName = $matches[1][1];

        return substr_replace(
            $dom,
            ' '.$attributesFormattedForHtmlElement,
            $positionOfFirstCharacterInTagName + $lengthOfTagName,
            0
        );
    }

    protected function escapeStringForHtml($subject)
    {
        if (is_string($subject) || is_numeric($subject)) {
            return htmlspecialchars($subject);
        }

        return htmlspecialchars(json_encode($subject));
    }
}
