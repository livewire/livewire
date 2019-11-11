<?php

namespace Livewire\HydrationMiddleware;

class AddAttributesToRootTagOfHtml
{
    public function __invoke($dom, $data)
    {
        $prefix = app('livewire')->prefix();

        $attributesFormattedForHtmlElement = collect($data)
            ->mapWithKeys(function ($value, $key) use ($prefix) {
                return ["{$prefix}:{$key}" => $this->escapeStringForHtml($value)];
            })->map(function ($value, $key) {
                return sprintf('%s="%s"', $key, $value);
            })->implode(' ');

        preg_match('/<([a-zA-Z0-9\-]*)/', $dom, $matches, PREG_OFFSET_CAPTURE);
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
