<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Exceptions\MultipleRootTagsInViewException;
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

        throw_if(
            $this->domHasMultipleRootTags($dom),
            new MultipleRootTagsInViewException
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

    protected function domHasMultipleRootTags($dom)
    {
        preg_match_all('/<\/?[a-zA-Z0-9\-]+/', $dom, $matches);
        $openTags = [];
        $rootTag = false;

        // Traversing open en closing tags.
        foreach($matches[0] as $tag) {
            if($tag[1] === '/') {
                $openTag = str_replace('/', '', $tag);
                $tagIndex = array_search($openTag, $openTags);
                if($tagIndex !== false) {
                    array_splice($openTags, 0, $tagIndex+1); // Will also remove self-closing tags
                }
            } else {
                if(empty($openTags) && $rootTag) {
                    // This is a second root tag
                    return true;
                } elseif (empty($openTags)) {
                    $rootTag = true;
                }

                array_unshift($openTags, $tag);
            }
        }
        
        return false;
    }
}
